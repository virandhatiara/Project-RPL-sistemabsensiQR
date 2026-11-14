<?php
/**
 * GuruDashboardModel — Data profil, jadwal, absensi untuk dashboard guru.
 */
class GuruDashboardModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /** Ambil profil guru berdasarkan user_id yang login */
    public function getProfilGuru(int $userId): ?array {
        $this->db->query("
            SELECT g.*, u.username 
            FROM guru g 
            JOIN users u ON g.user_id = u.id 
            WHERE g.user_id = :user_id
        ");
        $this->db->bind('user_id', $userId);
        return $this->db->single() ?: null;
    }

    /** Status absensi guru hari ini */
    public function getAbsensiHariIni(int $guruId, string $tanggal): ?array {
        $this->db->query("
            SELECT * FROM absensi_guru 
            WHERE guru_id = :guru_id AND tanggal = :tanggal
        ");
        $this->db->bind('guru_id', $guruId);
        $this->db->bind('tanggal', $tanggal);
        return $this->db->single() ?: null;
    }

    /** Jadwal mengajar hari ini */
    public function getJadwalHariIni(int $guruId, string $hari): array {
        $this->db->query("
            SELECT j.*, k.nama_kelas, g.nama as nama_guru
            FROM jadwal j
            JOIN kelas k ON j.kelas_id = k.id
            JOIN guru g ON j.guru_id = g.id
            WHERE j.guru_id = :guru_id AND j.hari = :hari
            ORDER BY j.jam_mulai ASC
        ");
        $this->db->bind('guru_id', $guruId);
        $this->db->bind('hari', $hari);
        return $this->db->resultSet();
    }

    /** Seluruh jadwal guru untuk seminggu */
    public function getJadwalMingguIni(int $guruId): array {
        $this->db->query("
            SELECT j.*, k.nama_kelas
            FROM jadwal j
            JOIN kelas k ON j.kelas_id = k.id
            WHERE j.guru_id = :guru_id
            ORDER BY FIELD(j.hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'), j.jam_mulai
        ");
        $this->db->bind('guru_id', $guruId);
        return $this->db->resultSet();
    }

    /** Riwayat absensi guru 30 hari terakhir */
    public function getRiwayatAbsensi(int $guruId, int $limit = 20): array {
        $this->db->query("
            SELECT * FROM absensi_guru 
            WHERE guru_id = :guru_id 
            ORDER BY tanggal DESC 
            LIMIT :limit
        ");
        $this->db->bind('guru_id', $guruId);
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    /** Rekap total hadir bulan ini */
    public function getRekapBulanIni(int $guruId): array {
        $this->db->query("
            SELECT 
                SUM(CASE WHEN status = 'hadir' OR status = 'terlambat' THEN 1 ELSE 0 END) as total_hadir,
                SUM(CASE WHEN jam_pulang IS NOT NULL THEN 1 ELSE 0 END) as sudah_pulang
            FROM absensi_guru
            WHERE guru_id = :guru_id 
              AND DATE_FORMAT(tanggal, '%Y-%m') = DATE_FORMAT(CURDATE(), '%Y-%m')
        ");
        $this->db->bind('guru_id', $guruId);
        $row = $this->db->single();
        return [
            'hadir_bulan_ini' => (int)($row['total_hadir'] ?? 0),
            'sudah_pulang'    => (int)($row['sudah_pulang'] ?? 0),
        ];
    }

    /** Update nomor HP / WhatsApp Guru */
    public function updateNoHp(int $guruId, ?string $noHp): bool {
        $clean = !empty($noHp) ? trim($noHp) : null;
        $this->db->query("UPDATE guru SET no_wa = :no_wa, no_hp = :no_hp WHERE id = :id");
        $this->db->bind('no_wa', $clean);
        $this->db->bind('no_hp', $clean);
        $this->db->bind('id', $guruId);
        return $this->db->execute();
    }
}
