<?php
class KepalaSekolahModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Statistik Hari Ini (Siswa & Guru)
    public function getRekapHariIni($tanggal) {
        $rekap = [
            'siswa' => ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0],
            'guru' => ['hadir' => 0] // Guru biasanya hanya masuk/pulang
        ];

        // Hitung Siswa (0=hadir, 1=sakit, 2=izin, 3=alpa)
        $this->db->query("SELECT status, COUNT(*) as total FROM absensi_siswa WHERE tanggal = :tanggal GROUP BY status");
        $this->db->bind('tanggal', $tanggal);
        $resSiswa = $this->db->resultSet();
        foreach ($resSiswa as $r) {
            $raw = $r['status'];
            $stInt = (int)$raw;
            switch ($stInt) {
                case 0: $rekap['siswa']['hadir'] += (int)$r['total']; break;
                case 1: $rekap['siswa']['sakit'] += (int)$r['total']; break;
                case 2: $rekap['siswa']['izin']  += (int)$r['total']; break;
                case 3: $rekap['siswa']['alpa']  += (int)$r['total']; break;
            }
        }

        // Hitung Guru
        $this->db->query("SELECT COUNT(*) as total FROM absensi_guru WHERE tanggal = :tanggal AND (status = 'hadir' OR status = 'terlambat')");
        $this->db->bind('tanggal', $tanggal);
        $rekap['guru']['hadir'] = $this->db->single()['total'] ?? 0;

        return $rekap;
    }

    // Statistik Tren Kehadiran Siswa 7 Hari Terakhir
    public function getTrenKehadiranSiswa() {
        $query = "SELECT tanggal, 
                         SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as total_hadir 
                  FROM absensi_siswa 
                  WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                  GROUP BY tanggal 
                  ORDER BY tanggal ASC";
        $this->db->query($query);
        return $this->db->resultSet();
    }

    // Laporan Absensi Siswa Dinamis
    public function getLaporanSiswa($filters) {
        $query = "SELECT a.*, s.nis, s.nama, k.nama_kelas 
                  FROM absensi_siswa a 
                  JOIN siswa s ON a.siswa_id = s.id 
                  JOIN kelas k ON s.kelas_id = k.id 
                  WHERE 1=1 ";
        
        $params = [];

        if (!empty($filters['tgl_awal']) && !empty($filters['tgl_akhir'])) {
            $query .= "AND a.tanggal BETWEEN :tgl_awal AND :tgl_akhir ";
            $params['tgl_awal'] = $filters['tgl_awal'];
            $params['tgl_akhir'] = $filters['tgl_akhir'];
        }
        if (!empty($filters['kelas_id'])) {
            $query .= "AND s.kelas_id = :kelas_id ";
            $params['kelas_id'] = $filters['kelas_id'];
        }
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query .= "AND a.status = :status ";
            $params['status'] = (int)$filters['status'];
        }
        if (!empty($filters['nama'])) {
            $query .= "AND s.nama LIKE :nama ";
            $params['nama'] = '%' . $filters['nama'] . '%';
        }

        $query .= "ORDER BY a.tanggal DESC, k.nama_kelas ASC, s.nama ASC";

        $this->db->query($query);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultSet();
    }

    // Laporan Absensi Guru Dinamis
    public function getLaporanGuru($filters) {
        $query = "SELECT a.*, g.nip, g.nama 
                  FROM absensi_guru a 
                  JOIN guru g ON a.guru_id = g.id 
                  WHERE 1=1 ";
        
        $params = [];

        if (!empty($filters['tgl_awal']) && !empty($filters['tgl_akhir'])) {
            $query .= "AND a.tanggal BETWEEN :tgl_awal AND :tgl_akhir ";
            $params['tgl_awal'] = $filters['tgl_awal'];
            $params['tgl_akhir'] = $filters['tgl_akhir'];
        }
        if (!empty($filters['nama'])) {
            $query .= "AND g.nama LIKE :nama ";
            $params['nama'] = '%' . $filters['nama'] . '%';
        }

        $query .= "ORDER BY a.tanggal DESC, g.nama ASC";

        $this->db->query($query);
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->resultSet();
    }
}
