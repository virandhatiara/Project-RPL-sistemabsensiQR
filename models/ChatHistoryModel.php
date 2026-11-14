<?php
class ChatHistoryModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Simpan satu record percakapan ke database.
     */
    public function simpan(int $userId, string $pertanyaan, string $jawaban): bool {
        $this->db->query("INSERT INTO chat_history (user_id, pertanyaan, jawaban) VALUES (:user_id, :pertanyaan, :jawaban)");
        $this->db->bind('user_id',   $userId);
        $this->db->bind('pertanyaan', $pertanyaan);
        $this->db->bind('jawaban',    $jawaban);
        return $this->db->execute();
    }

    /**
     * Ambil riwayat chat user tertentu (N pesan terakhir, dari yang terlama).
     */
    public function getRiwayat(int $userId, int $limit = 20): array {
        $this->db->query(
            "SELECT pertanyaan, jawaban, created_at FROM chat_history 
             WHERE user_id = :user_id 
             ORDER BY id DESC 
             LIMIT :limit"
        );
        $this->db->bind('user_id', $userId);
        $this->db->bind('limit',   $limit, PDO::PARAM_INT);
        $rows = $this->db->resultSet();
        // Kembalikan dalam urutan terlama ke terbaru untuk ditampilkan di UI
        return array_reverse($rows);
    }

    /**
     * Hapus seluruh riwayat chat milik user.
     */
    public function hapusRiwayat(int $userId): bool {
        $this->db->query("DELETE FROM chat_history WHERE user_id = :user_id");
        $this->db->bind('user_id', $userId);
        return $this->db->execute();
    }
}
