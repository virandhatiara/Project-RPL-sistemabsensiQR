<?php
class GuruModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllGuru($limit, $offset, $keyword = '') {
        $query = "SELECT g.*, u.username 
                  FROM guru g 
                  LEFT JOIN users u ON g.user_id = u.id ";
        
        if (!empty($keyword)) {
            $query .= "WHERE g.nip LIKE :keyword OR g.nama LIKE :keyword OR g.barcode_id LIKE :keyword OR g.no_wa LIKE :keyword OR g.no_hp LIKE :keyword ";
        }
        
        $query .= "ORDER BY g.id DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function countGuru($keyword = '') {
        $query = "SELECT COUNT(*) as total FROM guru g ";
        if (!empty($keyword)) {
            $query .= "WHERE g.nip LIKE :keyword OR g.nama LIKE :keyword OR g.barcode_id LIKE :keyword OR g.no_wa LIKE :keyword OR g.no_hp LIKE :keyword";
        }
        $this->db->query($query);
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        return $this->db->single()['total'];
    }

    public function getGuruById($id) {
        $this->db->query("SELECT g.*, u.username FROM guru g LEFT JOIN users u ON g.user_id = u.id WHERE g.id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getGuruByNip($nip, $excludeId = null) {
        $query = "SELECT * FROM guru WHERE nip = :nip";
        if ($excludeId) {
            $query .= " AND id != :excludeId";
        }
        $this->db->query($query);
        $this->db->bind('nip', $nip);
        if ($excludeId) {
            $this->db->bind('excludeId', $excludeId);
        }
        return $this->db->single();
    }

    public function getGuruByBarcode($barcode, $excludeId = null) {
        if(empty($barcode)) return false;
        $query = "SELECT * FROM guru WHERE barcode_id = :barcode";
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

    public function addGuru($data) {
        $query = "INSERT INTO guru (user_id, nip, nama, barcode_id, no_wa, no_hp) 
                  VALUES (:user_id, :nip, :nama, :barcode_id, :no_wa, :no_hp)";
        
        $nama = trim(preg_replace('/^(bapak|ibu|pak|bu)\.?\s+/i', '', $data['nama'] ?? ''));
        $noHp = !empty($data['no_wa']) ? trim($data['no_wa']) : (!empty($data['no_hp']) ? trim($data['no_hp']) : null);
        
        $this->db->query($query);
        $this->db->bind('user_id', empty($data['user_id']) ? null : $data['user_id']);
        $this->db->bind('nip', $data['nip']);
        $this->db->bind('nama', $nama);
        $this->db->bind('barcode_id', empty($data['barcode_id']) ? null : $data['barcode_id']);
        $this->db->bind('no_wa', $noHp);
        $this->db->bind('no_hp', $noHp);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateGuru($data) {
        $query = "UPDATE guru SET 
                  user_id = :user_id,
                  nip = :nip, 
                  nama = :nama, 
                  barcode_id = :barcode_id,
                  no_wa = :no_wa,
                  no_hp = :no_hp
                  WHERE id = :id";
        
        $nama = trim(preg_replace('/^(bapak|ibu|pak|bu)\.?\s+/i', '', $data['nama'] ?? ''));
        $noHp = !empty($data['no_wa']) ? trim($data['no_wa']) : (!empty($data['no_hp']) ? trim($data['no_hp']) : null);
        
        $this->db->query($query);
        $this->db->bind('id', $data['id']);
        $this->db->bind('user_id', empty($data['user_id']) ? null : $data['user_id']);
        $this->db->bind('nip', $data['nip']);
        $this->db->bind('nama', $nama);
        $this->db->bind('barcode_id', empty($data['barcode_id']) ? null : $data['barcode_id']);
        $this->db->bind('no_wa', $noHp);
        $this->db->bind('no_hp', $noHp);
        
        $this->db->execute();
        return true;
    }

    public function updateNoHp($id, $noHp) {
        $cleanHp = !empty($noHp) ? trim($noHp) : null;
        $this->db->query("UPDATE guru SET no_wa = :no_wa, no_hp = :no_hp WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->bind('no_wa', $cleanHp);
        $this->db->bind('no_hp', $cleanHp);
        return $this->db->execute();
    }

    public function deleteGuru($id) {
        $this->db->query("DELETE FROM guru WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return true;
    }
}
