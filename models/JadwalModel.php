<?php
class JadwalModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getAllJadwal($limit, $offset, $keyword = '') {
        $query = "SELECT j.*, k.nama_kelas, g.nama as nama_guru 
                  FROM jadwal j 
                  LEFT JOIN kelas k ON j.kelas_id = k.id 
                  LEFT JOIN guru g ON j.guru_id = g.id ";
        
        if (!empty($keyword)) {
            $query .= "WHERE k.nama_kelas LIKE :keyword 
                       OR g.nama LIKE :keyword 
                       OR j.hari LIKE :keyword 
                       OR j.mata_pelajaran LIKE :keyword ";
        }
        
        $query .= "ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai ASC LIMIT :limit OFFSET :offset";
        
        $this->db->query($query);
        
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        
        $this->db->bind('limit', $limit, PDO::PARAM_INT);
        $this->db->bind('offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    public function countJadwal($keyword = '') {
        $query = "SELECT COUNT(*) as total FROM jadwal j 
                  LEFT JOIN kelas k ON j.kelas_id = k.id 
                  LEFT JOIN guru g ON j.guru_id = g.id ";
        if (!empty($keyword)) {
            $query .= "WHERE k.nama_kelas LIKE :keyword 
                       OR g.nama LIKE :keyword 
                       OR j.hari LIKE :keyword 
                       OR j.mata_pelajaran LIKE :keyword";
        }
        $this->db->query($query);
        if (!empty($keyword)) {
            $this->db->bind('keyword', "%$keyword%");
        }
        return $this->db->single()['total'];
    }

    public function getJadwalById($id) {
        $this->db->query("SELECT * FROM jadwal WHERE id = :id");
        $this->db->bind('id', $id);
        return $this->db->single();
    }

    public function addJadwal($data) {
        $query = "INSERT INTO jadwal (kelas_id, guru_id, mata_pelajaran, hari, jam_mulai, jam_selesai) 
                  VALUES (:kelas_id, :guru_id, :mata_pelajaran, :hari, :jam_mulai, :jam_selesai)";
        
        $this->db->query($query);
        $this->db->bind('kelas_id', $data['kelas_id']);
        $this->db->bind('guru_id', $data['guru_id']);
        $this->db->bind('mata_pelajaran', $data['mata_pelajaran']);
        $this->db->bind('hari', $data['hari']);
        $this->db->bind('jam_mulai', $data['jam_mulai']);
        $this->db->bind('jam_selesai', $data['jam_selesai']);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateJadwal($data) {
        $query = "UPDATE jadwal SET 
                  kelas_id = :kelas_id, 
                  guru_id = :guru_id, 
                  mata_pelajaran = :mata_pelajaran,
                  hari = :hari, 
                  jam_mulai = :jam_mulai, 
                  jam_selesai = :jam_selesai 
                  WHERE id = :id";
        
        $this->db->query($query);
        $this->db->bind('id', $data['id']);
        $this->db->bind('kelas_id', $data['kelas_id']);
        $this->db->bind('guru_id', $data['guru_id']);
        $this->db->bind('mata_pelajaran', $data['mata_pelajaran']);
        $this->db->bind('hari', $data['hari']);
        $this->db->bind('jam_mulai', $data['jam_mulai']);
        $this->db->bind('jam_selesai', $data['jam_selesai']);
        
        $this->db->execute();
        return true;
    }

    public function deleteJadwal($id) {
        $this->db->query("DELETE FROM jadwal WHERE id = :id");
        $this->db->bind('id', $id);
        $this->db->execute();
        return true;
    }
}
