<?php
class WaliKelasModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Mendapatkan ID Kelas berdasarkan user_id (akun wali kelas yang login)
    public function getKelasBinaan($user_id) {
        $query = "SELECT k.*, g.nama as nama_wali 
                  FROM kelas k 
                  JOIN guru g ON k.wali_kelas_id = g.id 
                  WHERE g.user_id = :user_id";
        
        $this->db->query($query);
        $this->db->bind('user_id', $user_id);
        return $this->db->single();
    }

    // Mendapatkan seluruh siswa di kelas binaan beserta status absensi hari ini jika ada
    public function getSiswaWithAbsensiHariIni($kelas_id, $tanggal) {
        $query = "SELECT s.id, s.nis, s.nama, k.nama_kelas,
                         a.id as absensi_id, a.jam, a.status 
                  FROM siswa s 
                  JOIN kelas k ON s.kelas_id = k.id 
                  LEFT JOIN absensi_siswa a ON s.id = a.siswa_id AND a.tanggal = :tanggal 
                  WHERE s.kelas_id = :kelas_id 
                  ORDER BY s.nama ASC";
                  
        $this->db->query($query);
        $this->db->bind('kelas_id', $kelas_id);
        $this->db->bind('tanggal', $tanggal);
        return $this->db->resultSet();
    }

    // Mendapatkan riwayat absensi dengan filter (Pagination)
    public function getAbsensiDenganFilter($kelas_id, $limit, $offset, $tanggal = '', $nama = '', $status = '') {
        $query = "SELECT a.*, s.nis, s.nama, k.nama_kelas 
                  FROM absensi_siswa a 
                  JOIN siswa s ON a.siswa_id = s.id 
                  JOIN kelas k ON s.kelas_id = k.id 
                  WHERE s.kelas_id = :kelas_id ";
        
        if (!empty($tanggal)) {
            $query .= "AND a.tanggal = :tanggal ";
        }
        if (!empty($nama)) {
            $query .= "AND s.nama LIKE :nama ";
        }
        if ($status !== null && $status !== '') {
            $query .= "AND a.status = :status ";
        }
        
        $query .= "ORDER BY a.tanggal DESC, a.jam DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        $this->db->bind('kelas_id', $kelas_id);
        
        if (!empty($tanggal)) {
            $this->db->bind('tanggal', $tanggal);
        }
        if (!empty($nama)) {
            $this->db->bind('nama', "%$nama%");
        }
        if ($status !== null && $status !== '') {
            $this->db->bind('status', (int)$status, PDO::PARAM_INT);
        }
        
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    // Menghitung total data riwayat untuk pagination
    public function countAbsensiDenganFilter($kelas_id, $tanggal = '', $nama = '', $status = '') {
        $query = "SELECT COUNT(*) as total
                  FROM absensi_siswa a 
                  JOIN siswa s ON a.siswa_id = s.id 
                  WHERE s.kelas_id = :kelas_id ";
        
        if (!empty($tanggal)) {
            $query .= "AND a.tanggal = :tanggal ";
        }
        if (!empty($nama)) {
            $query .= "AND s.nama LIKE :nama ";
        }
        if ($status !== null && $status !== '') {
            $query .= "AND a.status = :status ";
        }
        
        $this->db->query($query);
        $this->db->bind('kelas_id', $kelas_id);
        
        if (!empty($tanggal)) {
            $this->db->bind('tanggal', $tanggal);
        }
        if (!empty($nama)) {
            $this->db->bind('nama', "%$nama%");
        }
        if ($status !== null && $status !== '') {
            $this->db->bind('status', (int)$status, PDO::PARAM_INT);
        }
        
        return $this->db->single()['total'];
    }

    // Rekap Data Per Siswa (0=hadir, 1=sakit, 2=izin, 3=alpa)
    public function getRekapSiswa($siswa_id) {
        $query = "SELECT 
                    SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as total_hadir,
                    SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as total_sakit,
                    SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as total_izin,
                    SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as total_alpa
                  FROM absensi_siswa 
                  WHERE siswa_id = :siswa_id";
        
        $this->db->query($query);
        $this->db->bind('siswa_id', $siswa_id);
        $result = $this->db->single();
        
        // Handle null if no data
        return [
            'total_hadir' => $result['total_hadir'] ?? 0,
            'total_sakit' => $result['total_sakit'] ?? 0,
            'total_izin' =>  $result['total_izin']  ?? 0,
            'total_alpa' =>  $result['total_alpa']  ?? 0
        ];
    }
}
