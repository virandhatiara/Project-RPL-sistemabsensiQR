<?php
class KelasModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllKelas($limit, $offset, $keyword = '') {
        $query = "SELECT k.*, g.nama as nama_wali 
                  FROM kelas k 
                  LEFT JOIN guru g ON k.wali_kelas_id = g.id ";
        
        if (!empty($keyword)) {
            $query .= "WHERE k.nama_kelas LIKE :keyword OR g.nama LIKE :keyword ";
        }
        
        $query .= "ORDER BY k.id DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function getAllKelasList() {
        $this->db->query("SELECT * FROM kelas ORDER BY nama_kelas ASC");
        return $this->db->resultSet();
    }

    public function countKelas($keyword = '') {
        $query = "SELECT COUNT(*) as total FROM kelas k LEFT JOIN guru g ON k.wali_kelas_id = g.id ";
        if (!empty($keyword)) {
            $query .= "WHERE k.nama_kelas LIKE :keyword OR g.nama LIKE :keyword";
        }
        $this->db->query($query);
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        return $this->db->single()['total'];
    }

    public function getKelasById($id) {
        $this->db->query("SELECT * FROM kelas WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getKelasByName($nama_kelas, $excludeId = null) {
        $query = "SELECT * FROM kelas WHERE nama_kelas = :nama_kelas";
        if ($excludeId) {
            $query .= " AND id != :excludeId";
        }
        $this->db->query($query);
        $this->db->bind('nama_kelas', $nama_kelas);
        if ($excludeId) {
            $this->db->bind('excludeId', $excludeId);
        }
        return $this->db->single();
    }

    public function addKelas($data) {
        $query = "INSERT INTO kelas (nama_kelas, wali_kelas_id) 
                  VALUES (:nama_kelas, :wali_kelas_id)";
        
        $this->db->query($query);
        $this->db->bind('nama_kelas', $data['nama_kelas']);
        $this->db->bind('wali_kelas_id', empty($data['wali_kelas_id']) ? null : $data['wali_kelas_id']);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateKelas($data) {
        $query = "UPDATE kelas SET 
                  nama_kelas = :nama_kelas, 
                  wali_kelas_id = :wali_kelas_id
                  WHERE id = :id";
        
        $this->db->query($query);
        $this->db->bind('id', $data['id']);
        $this->db->bind('nama_kelas', $data['nama_kelas']);
        $this->db->bind('wali_kelas_id', empty($data['wali_kelas_id']) ? null : $data['wali_kelas_id']);
        
        $this->db->execute();
        return true;
    }

    public function deleteKelas($id) {
        $this->db->query("DELETE FROM kelas WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return true;
    }
}
