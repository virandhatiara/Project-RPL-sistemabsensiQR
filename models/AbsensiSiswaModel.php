<?php
class AbsensiSiswaModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Cek apakah siswa sudah absen pada tanggal tertentu
    public function cekAbsensiHariIni($siswa_id, $tanggal) {
        $this->db->query("SELECT * FROM absensi_siswa WHERE siswa_id = :siswa_id AND tanggal = :tanggal");
        $this->db->bind('siswa_id', $siswa_id);
        $this->db->bind('tanggal', $tanggal);
        return $this->db->single();
    }

    // Ambil data absensi berdasarkan ID
    public function getAbsensiById($id) {
        $query = "SELECT a.*, s.nis, s.nama, s.kelas_id, k.nama_kelas 
                  FROM absensi_siswa a 
                  JOIN siswa s ON a.siswa_id = s.id 
                  LEFT JOIN kelas k ON s.kelas_id = k.id 
                  WHERE a.id = :id LIMIT 1";
        $this->db->query($query);
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    // Tambah data absensi baru
    public function tambahAbsensi($data) {
        $query = "INSERT INTO absensi_siswa (siswa_id, tanggal, jam, status) 
                  VALUES (:siswa_id, :tanggal, :jam, :status)";
        
        $this->db->query($query);
        $this->db->bind('siswa_id', $data['siswa_id']);
        $this->db->bind('tanggal', $data['tanggal']);
        $this->db->bind('jam', $data['jam']);
        $this->db->bind('status', $data['status']);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    // Tambah atau perbarui data absensi jika siswa sudah tercatat pada hari tersebut
    public function tambahOrUpdateAbsensi($data) {
        $existing = $this->cekAbsensiHariIni($data['siswa_id'], $data['tanggal']);
        if ($existing) {
            $query = "UPDATE absensi_siswa 
                      SET jam = :jam, status = :status 
                      WHERE id = :id";
            $this->db->query($query);
            $this->db->bind('jam', $data['jam']);
            $this->db->bind('status', $data['status']);
            $this->db->bind('id', $existing['id']);
            $this->db->execute();
            return $existing['id'];
        } else {
            return $this->tambahAbsensi($data);
        }
    }

    // Perbarui status, jam, atau tanggal absensi
    public function updateAbsensi($id, $data) {
        $query = "UPDATE absensi_siswa 
                  SET tanggal = :tanggal, jam = :jam, status = :status 
                  WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->bind('tanggal', $data['tanggal']);
        $this->db->bind('jam', $data['jam']);
        $this->db->bind('status', $data['status']);
        $this->db->execute();
        return true;
    }

    // Hapus data absensi
    public function deleteAbsensi($id) {
        $this->db->query("DELETE FROM absensi_siswa WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return true;
    }

    // Ambil data absensi siswa dengan filter lengkap (tanggal, kelas, status, keyword)
    public function getAbsensiFiltered($tanggal = '', $kelas_id = null, $status = null, $keyword = null, $limit = 20, $offset = 0) {
        $query = "SELECT a.*, s.nis, s.nama, s.kelas_id, k.nama_kelas 
                  FROM absensi_siswa a 
                  JOIN siswa s ON a.siswa_id = s.id 
                  LEFT JOIN kelas k ON s.kelas_id = k.id 
                  WHERE 1=1 ";

        if (!empty($tanggal)) {
            $query .= " AND a.tanggal = :tanggal ";
        }
        if (!empty($kelas_id)) {
            $query .= " AND s.kelas_id = :kelas_id ";
        }
        if ($status !== null && $status !== '') {
            $query .= " AND a.status = :status ";
        }
        if (!empty($keyword)) {
            $query .= " AND (s.nama LIKE :keyword OR s.nis LIKE :keyword OR k.nama_kelas LIKE :keyword) ";
        }

        $query .= " ORDER BY a.tanggal DESC, a.jam DESC, s.nama ASC LIMIT :limit OFFSET :offset";

        $this->db->query($query);

        if (!empty($tanggal)) $this->db->bind('tanggal', $tanggal);
        if (!empty($kelas_id)) $this->db->bind('kelas_id', $kelas_id);
        if ($status !== null && $status !== '') $this->db->bind('status', (int)$status, PDO::PARAM_INT);
        if (!empty($keyword)) $this->db->bind('keyword', "%$keyword%");

        $this->db->bind('limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind('offset', (int)$offset, PDO::PARAM_INT);

        return $this->db->resultSet();
    }

    // Hitung total data absensi terfilter
    public function countAbsensiFiltered($tanggal = '', $kelas_id = null, $status = null, $keyword = null) {
        $query = "SELECT COUNT(*) as total 
                  FROM absensi_siswa a 
                  JOIN siswa s ON a.siswa_id = s.id 
                  LEFT JOIN kelas k ON s.kelas_id = k.id 
                  WHERE 1=1 ";

        if (!empty($tanggal)) {
            $query .= " AND a.tanggal = :tanggal ";
        }
        if (!empty($kelas_id)) {
            $query .= " AND s.kelas_id = :kelas_id ";
        }
        if ($status !== null && $status !== '') {
            $query .= " AND a.status = :status ";
        }
        if (!empty($keyword)) {
            $query .= " AND (s.nama LIKE :keyword OR s.nis LIKE :keyword OR k.nama_kelas LIKE :keyword) ";
        }

        $this->db->query($query);

        if (!empty($tanggal)) $this->db->bind('tanggal', $tanggal);
        if (!empty($kelas_id)) $this->db->bind('kelas_id', $kelas_id);
        if ($status !== null && $status !== '') $this->db->bind('status', (int)$status, PDO::PARAM_INT);
        if (!empty($keyword)) $this->db->bind('keyword', "%$keyword%");

        $row = $this->db->single();
        return $row ? (int)$row['total'] : 0;
    }

    // Ringkasan statistik per status kehadiran (0=Hadir, 1=Sakit, 2=Izin, 3=Alpha)
    public function getStatistikKehadiran($tanggal, $kelas_id = null) {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN a.status = 0 THEN 1 ELSE 0 END) as total_hadir,
                    SUM(CASE WHEN a.status = 0 THEN 1 ELSE 0 END) as tepat_waktu,
                    0 as terlambat,
                    SUM(CASE WHEN a.status = 1 THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN a.status = 2 THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN a.status = 3 THEN 1 ELSE 0 END) as alpa
                  FROM absensi_siswa a 
                  JOIN siswa s ON a.siswa_id = s.id 
                  WHERE a.tanggal = :tanggal";
        
        if (!empty($kelas_id)) {
            $query .= " AND s.kelas_id = :kelas_id";
        }

        $this->db->query($query);
        $this->db->bind('tanggal', $tanggal);
        if (!empty($kelas_id)) {
            $this->db->bind('kelas_id', $kelas_id);
        }

        return $this->db->single();
    }

    // Mendapatkan riwayat absensi siswa
    public function getRiwayatAbsensi($siswa_id, $limit = 10) {
        $this->db->query("SELECT * FROM absensi_siswa WHERE siswa_id = :siswa_id ORDER BY tanggal DESC, jam DESC LIMIT :limit");
        $this->db->bind('siswa_id', $siswa_id);
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        return $this->db->resultSet();
    }

    // Otomatis masukkan siswa yang belum absen (tidak ada hadir, sakit, izin) sebagai Alpha (status = 3)
    public function autoDataAlphaSiswa($tanggal = null) {
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }
        $query = "INSERT INTO absensi_siswa (siswa_id, tanggal, jam, status)
                  SELECT s.id, :tanggal, '07:00:00', 3
                  FROM siswa s
                  WHERE s.id NOT IN (
                      SELECT a.siswa_id FROM absensi_siswa a WHERE a.tanggal = :tanggal_cek
                  )";
        $this->db->query($query);
        $this->db->bind('tanggal', $tanggal);
        $this->db->bind('tanggal_cek', $tanggal);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
