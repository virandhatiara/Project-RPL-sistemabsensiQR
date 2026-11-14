<?php
class UserModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllUser($limit, $offset, $keyword = '') {
        $query = "SELECT * FROM users ";
        
        if (!empty($keyword)) {
            $query .= "WHERE username LIKE :keyword OR nama LIKE :keyword OR role LIKE :keyword ";
        }
        
        $query .= "ORDER BY id DESC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function countUser($keyword = '') {
        $query = "SELECT COUNT(*) as total FROM users ";
        if (!empty($keyword)) {
            $query .= "WHERE username LIKE :keyword OR nama LIKE :keyword OR role LIKE :keyword";
        }
        $this->db->query($query);
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        return $this->db->single()['total'];
    }

    public function getUserById($id) {
        $this->db->query("SELECT * FROM users WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function getUserByUsername($username, $excludeId = null) {
        $query = "SELECT * FROM users WHERE username = :username";
        if ($excludeId) {
            $query .= " AND id != :excludeId";
        }
        $this->db->query($query);
        $this->db->bind('username', $username);
        if ($excludeId) {
            $this->db->bind('excludeId', $excludeId);
        }
        return $this->db->single();
    }

    public function addUser($data) {
        $query = "INSERT INTO users (username, nama, password, role, is_active) 
                  VALUES (:username, :nama, :password, :role, :is_active)";
        
        $this->db->query($query);
        $this->db->bind('username', $data['username']);
        $this->db->bind('nama', !empty($data['nama']) ? trim($data['nama']) : null);
        $this->db->bind('password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind('role', $data['role']);
        $this->db->bind('is_active', isset($data['is_active']) ? $data['is_active'] : 1);
        
        $this->db->execute();
        
        $this->db->query("SELECT LAST_INSERT_ID() as id");
        $result = $this->db->single();
        return $result['id'];
    }

    public function updateUser($data) {
        $query = "UPDATE users SET 
                  username = :username, 
                  nama = :nama,
                  role = :role, 
                  is_active = :is_active";
        
        if (!empty($data['password'])) {
            $query .= ", password = :password";
        }
        
        $query .= " WHERE id = :id";
        
        $this->db->query($query);
        $this->db->bind('id', $data['id']);
        $this->db->bind('username', $data['username']);
        $this->db->bind('nama', !empty($data['nama']) ? trim($data['nama']) : null);
        $this->db->bind('role', $data['role']);
        $this->db->bind('is_active', $data['is_active']);
        
        if (!empty($data['password'])) {
            $this->db->bind('password', password_hash($data['password'], PASSWORD_DEFAULT));
        }
        
        $this->db->execute();
        return true;
    }

    public function deleteUser($id) {
        $this->db->query("DELETE FROM users WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return true;
    }
}
