<?php
/**
 * AdminDashboardModel — Menyediakan semua data agregat untuk Admin Dashboard.
 * Hanya digunakan oleh AdminController, tidak ada di role lain.
 */
class AdminDashboardModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /** Ringkasan total keseluruhan */
    public function getTotalRingkasan(): array {
        $result = [];

        $this->db->query("SELECT COUNT(*) as total FROM siswa");
        $result['total_siswa'] = $this->db->single()['total'] ?? 0;

        $this->db->query("SELECT COUNT(*) as total FROM guru");
        $result['total_guru'] = $this->db->single()['total'] ?? 0;

        $this->db->query("SELECT COUNT(*) as total FROM kelas");
        $result['total_kelas'] = $this->db->single()['total'] ?? 0;

        return $result;
    }

    /** Absensi siswa hari ini (jumlah per status: 0=hadir, 1=sakit, 2=izin, 3=alpa) */
    public function getAbsensiSiswaHariIni(string $tanggal): array {
        $this->db->query("
            SELECT status, COUNT(*) as total 
            FROM absensi_siswa 
            WHERE tanggal = :tanggal 
            GROUP BY status
        ");
        $this->db->bind('tanggal', $tanggal);
        $rows = $this->db->resultSet();

        $hasil = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0, 'terlambat' => 0, 'total' => 0];
        foreach ($rows as $r) {
            $raw = $r['status'];
            $stInt = (int)$raw;
            switch ($stInt) {
                case 0: $hasil['hadir'] += (int)$r['total']; break;
                case 1: $hasil['sakit'] += (int)$r['total']; break;
                case 2: $hasil['izin']  += (int)$r['total']; break;
                case 3: $hasil['alpa']  += (int)$r['total']; break;
            }
            $hasil['total'] += (int)$r['total'];
        }
        return $hasil;
    }

    /** Absensi guru hari ini (hadir & belum hadir) */
    public function getAbsensiGuruHariIni(string $tanggal): array {
        $this->db->query("SELECT COUNT(*) as total FROM guru");
        $totalGuru = (int)($this->db->single()['total'] ?? 0);

        $this->db->query("SELECT COUNT(*) as total FROM absensi_guru WHERE tanggal = :tanggal AND (status = 'hadir' OR status = 'terlambat')");
        $this->db->bind('tanggal', $tanggal);
        $hadirGuru = (int)($this->db->single()['total'] ?? 0);

        return [
            'hadir'       => $hadirGuru,
            'tidak_hadir' => max(0, $totalGuru - $hadirGuru),
            'total'       => $totalGuru,
        ];
    }

    /** Daftar siswa yang tidak hadir hari ini (alpa=3 / tidak absen) */
    public function getSiswaTidakHadir(string $tanggal): array {
        $this->db->query("
            SELECT s.nis, s.nama, k.nama_kelas, 
                   COALESCE(a.status, 'belum_absen') as status
            FROM siswa s
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN absensi_siswa a ON a.siswa_id = s.id AND a.tanggal = :tanggal
            WHERE a.status IS NULL OR a.status = 3 OR a.status = 'alpa'
            ORDER BY k.nama_kelas, s.nama
            LIMIT 10
        ");
        $this->db->bind('tanggal', $tanggal);
        return $this->db->resultSet();
    }

    /** Data tren kehadiran siswa 7 hari terakhir untuk grafik */
    public function getTrenKehadiran(): array {
        $this->db->query("
            SELECT DATE_FORMAT(tanggal, '%d/%m') as label,
                   SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as hadir,
                   SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as alpa
            FROM absensi_siswa
            WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
            GROUP BY tanggal
            ORDER BY tanggal ASC
        ");
        return $this->db->resultSet();
    }
}
