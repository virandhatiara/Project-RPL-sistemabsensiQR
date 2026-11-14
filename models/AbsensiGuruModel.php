<?php
class AbsensiGuruModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Cek apakah guru sudah absen pada tanggal tertentu
    public function cekAbsensiHariIni($guru_id, $tanggal) {
        $this->db->query("SELECT * FROM absensi_guru WHERE guru_id = :guru_id AND tanggal = :tanggal");
        $this->db->bind('guru_id', $guru_id);
        $this->db->bind('tanggal', $tanggal);
        return $this->db->single();
    }

    // Tambah data jam masuk (Scan Pertama)
    public function catatJamMasuk($data) {
        $query = "INSERT INTO absensi_guru (guru_id, tanggal, jam_masuk, status) 
                  VALUES (:guru_id, :tanggal, :jam_masuk, :status)";
        
        $this->db->query($query);
        $this->db->bind('guru_id', $data['guru_id']);
        $this->db->bind('tanggal', $data['tanggal']);
        $this->db->bind('jam_masuk', $data['jam']);
        $this->db->bind('status', $data['status']);
        
        $this->db->execute();
        return $this->db->rowCount();
    }

    // Update data jam pulang (Scan Kedua)
    public function catatJamPulang($id, $jam_pulang) {
        $query = "UPDATE absensi_guru SET jam_pulang = :jam_pulang WHERE id = :id";
        
        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->bind('jam_pulang', $jam_pulang);
        
        $this->db->execute();
        return true;
    }

    // Perbarui status dan jam masuk guru (misal sebelumnya otomatis ditandai Alpa)
    public function updateStatusMasuk($id, $jam_masuk, $status) {
        $query = "UPDATE absensi_guru SET jam_masuk = :jam_masuk, status = :status WHERE id = :id";
        $this->db->query($query);
        $this->db->bind('id', $id);
        $this->db->bind('jam_masuk', $jam_masuk);
        $this->db->bind('status', $status);
        $this->db->execute();
        return true;
    }

    // Otomatis masukkan guru yang belum absen (tidak ada hadir, sakit, izin) sebagai Alpha (status = 'alpa')
    public function autoDataAlphaGuru($tanggal = null) {
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }
        $query = "INSERT INTO absensi_guru (guru_id, tanggal, jam_masuk, status)
                  SELECT g.id, :tanggal, '07:00:00', 'alpa'
                  FROM guru g
                  WHERE g.id NOT IN (
                      SELECT ag.guru_id FROM absensi_guru ag WHERE ag.tanggal = :tanggal_cek
                  )";
        $this->db->query($query);
        $this->db->bind('tanggal', $tanggal);
        $this->db->bind('tanggal_cek', $tanggal);
        $this->db->execute();
        return $this->db->rowCount();
    }
}
