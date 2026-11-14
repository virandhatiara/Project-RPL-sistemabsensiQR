<?php
class User {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Cari akun user berdasarkan Username, NIS (Siswa), NIP (Guru), atau Barcode ID
     * Memungkinkan siswa login dengan NIS atau username, dan guru login dengan NIP atau username.
     */
    public function getUserByUsername($identifier) {
        $identifier = trim((string)$identifier);
        if ($identifier === '') {
            return false;
        }

        // 1. Cek langsung ke kolom username di tabel users (case-insensitive)
        $this->db->query("SELECT * FROM users WHERE LOWER(username) = LOWER(:username) LIMIT 1");
        $this->db->bind('username', $identifier);
        $user = $this->db->single();
        if ($user) {
            return $user;
        }

        // 2. Cek ke tabel siswa via NIS
        $this->db->query("
            SELECT u.* 
            FROM users u
            INNER JOIN siswa s ON s.user_id = u.id
            WHERE s.nis = :nis
            LIMIT 1
        ");
        $this->db->bind('nis', $identifier);
        $user = $this->db->single();
        if ($user) {
            return $user;
        }

        // 3. Cek ke tabel guru via NIP
        $this->db->query("
            SELECT u.* 
            FROM users u
            INNER JOIN guru g ON g.user_id = u.id
            WHERE g.nip = :nip
            LIMIT 1
        ");
        $this->db->bind('nip', $identifier);
        $user = $this->db->single();
        if ($user) {
            return $user;
        }

        // 4. Cek via Barcode ID Siswa
        $this->db->query("
            SELECT u.* 
            FROM users u
            INNER JOIN siswa s ON s.user_id = u.id
            WHERE s.barcode_id = :barcode
            LIMIT 1
        ");
        $this->db->bind('barcode', $identifier);
        $user = $this->db->single();
        if ($user) {
            return $user;
        }

        // 5. Cek via Barcode ID Guru
        $this->db->query("
            SELECT u.* 
            FROM users u
            INNER JOIN guru g ON g.user_id = u.id
            WHERE g.barcode_id = :barcode
            LIMIT 1
        ");
        $this->db->bind('barcode', $identifier);
        $user = $this->db->single();
        if ($user) {
            return $user;
        }

        return false;
    }
}
