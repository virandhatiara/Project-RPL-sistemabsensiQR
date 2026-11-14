<?php
class SiswaModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllSiswa($limit, $offset, $keyword = '') {
        $query = "SELECT s.*, k.nama_kelas, u.username 
                  FROM siswa s 
                  LEFT JOIN kelas k ON s.kelas_id = k.id 
                  LEFT JOIN users u ON s.user_id = u.id ";
        
        if (!empty($keyword)) {
            $query .= "WHERE s.nis LIKE :keyword OR s.nama LIKE :keyword OR s.barcode_id LIKE :keyword OR k.nama_kelas LIKE :keyword OR s.nama_ortu LIKE :keyword OR s.no_hp_ortu LIKE :keyword ";
        }
        
        $query .= "ORDER BY s.id DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function countSiswa($keyword = '') {
        $query = "SELECT COUNT(*) as total FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id ";
        if (!empty($keyword)) {
            $query .= "WHERE s.nis LIKE :keyword OR s.nama LIKE :keyword OR s.barcode_id LIKE :keyword OR k.nama_kelas LIKE :keyword OR s.nama_ortu LIKE :keyword OR s.no_hp_ortu LIKE :keyword";
        }
        $this->db->query($query);
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        return $this->db->single()['total'];
    }

    public function getSiswaById($id) {
        $this->db->query("SELECT s.*, u.username FROM siswa s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getSiswaByNis($nis, $excludeId = null) {
        $query = "SELECT * FROM siswa WHERE nis = :nis";
        if ($excludeId) {
            $query .= " AND id != :excludeId";
        }
        $this->db->query($query);
        $this->db->bind('nis', $nis);
        if ($excludeId) {
            $this->db->bind('excludeId', $excludeId);
        }
        return $this->db->single();
    }

    public function getSiswaByBarcode($barcode, $excludeId = null) {
        if(empty($barcode)) return false;
        $query = "SELECT * FROM siswa WHERE barcode_id = :barcode";
        if ($excludeId) {
            $query .= " AND id != :excludeId";
        }
        $this->db->query($query);
        $this->db->bind('barcode', $barcode);
        if ($excludeId) {
            $this->db->bind('excludeId', $excludeId);
        }
        return $this->db->single();
    }

    public function addSiswa($data) {
        $query = "INSERT INTO siswa (user_id, nis, nama, kelas_id, barcode_id, no_wa, nama_ortu, no_hp_ortu) 
                  VALUES (:user_id, :nis, :nama, :kelas_id, :barcode_id, :no_wa, :nama_ortu, :no_hp_ortu)";
        
        $noWaOrtu = !empty($data['no_hp_ortu']) ? trim($data['no_hp_ortu']) : (!empty($data['no_wa']) ? trim($data['no_wa']) : null);
        
        $this->db->query($query);
        $this->db->bind('user_id', empty($data['user_id']) ? null : $data['user_id']);
        $this->db->bind('nis', $data['nis']);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('kelas_id', empty($data['kelas_id']) ? null : $data['kelas_id']);
        $this->db->bind('barcode_id', empty($data['barcode_id']) ? null : $data['barcode_id']);
        $this->db->bind('no_wa', $noWaOrtu);
        $this->db->bind('nama_ortu', !empty($data['nama_ortu']) ? trim($data['nama_ortu']) : null);
        $this->db->bind('no_hp_ortu', $noWaOrtu);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateSiswa($data) {
        $query = "UPDATE siswa SET 
                  user_id = :user_id,
                  nis = :nis, 
                  nama = :nama, 
                  kelas_id = :kelas_id, 
                  barcode_id = :barcode_id,
                  no_wa = :no_wa,
                  nama_ortu = :nama_ortu,
                  no_hp_ortu = :no_hp_ortu
                  WHERE id = :id";
        
        $noWaOrtu = !empty($data['no_hp_ortu']) ? trim($data['no_hp_ortu']) : (!empty($data['no_wa']) ? trim($data['no_wa']) : null);

        $this->db->query($query);
        $this->db->bind('id', $data['id']);
        $this->db->bind('user_id', empty($data['user_id']) ? null : $data['user_id']);
        $this->db->bind('nis', $data['nis']);
        $this->db->bind('nama', $data['nama']);
        $this->db->bind('kelas_id', empty($data['kelas_id']) ? null : $data['kelas_id']);
        $this->db->bind('barcode_id', empty($data['barcode_id']) ? null : $data['barcode_id']);
        $this->db->bind('no_wa', $noWaOrtu);
        $this->db->bind('nama_ortu', !empty($data['nama_ortu']) ? trim($data['nama_ortu']) : null);
        $this->db->bind('no_hp_ortu', $noWaOrtu);
        
        $this->db->execute();
        return true;
    }

    public function deleteSiswa($id) {
        $this->db->query("DELETE FROM siswa WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return true;
    }
    
    // Ambil semua daftar siswa terurut berdasarkan kelas dan nama (untuk pilihan dropdown absensi admin)
    public function getAllSiswaList() {
        $query = "SELECT s.id, s.nis, s.nama, s.kelas_id, k.nama_kelas 
                  FROM siswa s 
                  LEFT JOIN kelas k ON s.kelas_id = k.id 
                  ORDER BY k.nama_kelas ASC, s.nama ASC";
        $this->db->query($query);
        return $this->db->resultSet();
    }

    // For Dashboard
    public function getDashboardStats() {
        // Example stats for Siswa dashboard
        return [];
    }
}
