<?php
class SiswaPortalModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Ambil Profil Siswa (Join Kelas) berdasarkan user_id login
    public function getProfilSiswa($user_id) {
        $query = "SELECT s.*, k.nama_kelas 
                  FROM siswa s 
                  LEFT JOIN kelas k ON s.kelas_id = k.id 
                  WHERE s.user_id = :user_id";
        
        $this->db->query($query);
        $this->db->bind('user_id', $user_id);
        return $this->db->single();
    }

    // Kalkulasi Total Hadir, Sakit, Izin, Alpa (0=hadir, 1=sakit, 2=izin, 3=alpa)
    public function getRekapAbsensi($siswa_id) {
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
        
        return [
            'total_hadir' => $result['total_hadir'] ?? 0,
            'total_sakit' => $result['total_sakit'] ?? 0,
            'total_izin'  => $result['total_izin'] ?? 0,
            'total_alpa'  => $result['total_alpa'] ?? 0
        ];
    }

    // Ambil Riwayat dengan dukungan Filter yang beragam (Rentang Tanggal, Bulan, Status)
    public function getRiwayatFilter($siswa_id, $filters) {
        $query = "SELECT * FROM absensi_siswa WHERE siswa_id = :siswa_id ";
        $params = ['siswa_id' => $siswa_id];

        // Filter Rentang Tanggal
        if (!empty($filters['tgl_awal']) && !empty($filters['tgl_akhir'])) {
            $query .= "AND tanggal BETWEEN :tgl_awal AND :tgl_akhir ";
            $params['tgl_awal'] = $filters['tgl_awal'];
            $params['tgl_akhir'] = $filters['tgl_akhir'];
        } 
        // Jika rentang tanggal tidak diisi, tapi bulan dipilih
        elseif (!empty($filters['bulan'])) {
            // $filters['bulan'] berformat "YYYY-MM"
            $query .= "AND DATE_FORMAT(tanggal, '%Y-%m') = :bulan ";
            $params['bulan'] = $filters['bulan'];
        }

        // Filter Status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query .= "AND status = :status ";
            $params['status'] = (int)$filters['status'];
        }

        $query .= "ORDER BY tanggal DESC, jam DESC";

        $this->db->query($query);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultSet();
    }

    // Ambil Jadwal Belajar Kelas Hari Ini
    public function getJadwalHariIni($kelas_id, $hari) {
        $query = "SELECT j.*, g.nama as nama_guru, g.nip 
                  FROM jadwal j 
                  JOIN guru g ON j.guru_id = g.id 
                  WHERE j.kelas_id = :kelas_id AND j.hari = :hari 
                  ORDER BY j.jam_mulai ASC";
        $this->db->query($query);
        $this->db->bind('kelas_id', $kelas_id);
        $this->db->bind('hari', $hari);
        return $this->db->resultSet();
    }

    // Ambil Seluruh Jadwal Belajar Kelas (Seminggu)
    public function getJadwalSeminggu($kelas_id) {
        $query = "SELECT j.*, g.nama as nama_guru, g.nip 
                  FROM jadwal j 
                  JOIN guru g ON j.guru_id = g.id 
                  WHERE j.kelas_id = :kelas_id 
                  ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai ASC";
        $this->db->query($query);
        $this->db->bind('kelas_id', $kelas_id);
        return $this->db->resultSet();
    }
}
