<?php
require_once '../app/services/AiService.php';

class ChatbotController extends Controller {

    public function __construct() {
        // Hanya perlu sesi valid — tidak perlu parent CSRF karena endpoint ini
        // memakai validasi CSRF token sendiri (via X-CSRF-Token header dari JS).
        Csrf::getToken();
    }

    /**
     * Endpoint AJAX: POST /chatbot/send
     * Body JSON: { "message": "...", "history": [...] }
     */
    public function send() {
        header('Content-Type: application/json; charset=utf-8');

        // Wajib login untuk menggunakan chatbot
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            exit;
        }

        // Validasi CSRF via header (standar untuk AJAX)
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo json_encode(['error' => 'CSRF token tidak valid.']);
            exit;
        }

        // Parse request body
        $body    = json_decode(file_get_contents('php://input'), true);
        $message = trim(strip_tags($body['message'] ?? ''));
        $history = $body['history'] ?? [];

        if (empty($message)) {
            http_response_code(400);
            echo json_encode(['error' => 'Pesan tidak boleh kosong.']);
            exit;
        }

        if (strlen($message) > 1000) {
            http_response_code(400);
            echo json_encode(['error' => 'Pesan terlalu panjang (maks 1000 karakter).']);
            exit;
        }

        $userId   = (int)$_SESSION['user_id'];
        $role     = $_SESSION['role'] ?? 'siswa';
        $username = $_SESSION['username'] ?? 'Pengguna';

        // Guardrail Pre-check: Larangan keras kode pemrograman (termasuk Python)
        $lowerMsg = strtolower($message);
        if (preg_match('/\b(python|coding|script|code|buatkan kode|bikin kode|syntax|program)\b/i', $lowerMsg) ||
            str_contains($lowerMsg, 'def ') || str_contains($lowerMsg, 'import ') || str_contains($lowerMsg, 'print(')) {
            $jawaban = "🚫 **Akses Ditolak:**\n\nMaaf, saya tidak dapat membuat atau menampilkan kode pemrograman apapun (termasuk Python). Saya adalah asisten Sistem Absensi yang terhubung langsung ke database sekolah.";
            
            $chatModel = $this->model('ChatHistoryModel');
            $chatModel->simpan($userId, $message, $jawaban);

            echo json_encode([
                'answer' => $jawaban,
                'isMock' => false,
            ]);
            exit;
        }

        $chatbotModel = $this->model('ChatbotModel');

        // 1. Cek Pertanyaan Identitas Diri (Database Profil)
        if ($chatbotModel->isIdentityQuestion($message)) {
            $jawaban = $chatbotModel->getIdentityResponse($userId, $role, $username);
            $chatModel = $this->model('ChatHistoryModel');
            $chatModel->simpan($userId, $message, $jawaban);

            echo json_encode([
                'answer' => $jawaban,
                'isMock' => false,
            ]);
            exit;
        }

        // 2. Cek Pertanyaan Langsung Absensi, Rekapitulasi, Riwayat, atau Jadwal (Akses Database Langsung)
        $directAnswer = $chatbotModel->getDirectAttendanceResponse($userId, $role, $username, $message);
        if ($directAnswer !== null) {
            $chatModel = $this->model('ChatHistoryModel');
            $chatModel->simpan($userId, $message, $directAnswer);

            echo json_encode([
                'answer' => $directAnswer,
                'isMock' => false,
            ]);
            exit;
        }

        // 3. Hubungkan Chatbot ke Database secara real-time via ChatbotModel untuk pertanyaan umum / AI
        $dbContext = $chatbotModel->buildDatabaseContext($userId, $role, $username, $message);

        // Bangun system prompt yang terhubung dengan database
        $systemPrompt = $this->buildSystemPrompt($role, $username, $dbContext);

        // Bangun messages array — riwayat lama + pesan baru
        $messages = [];
        foreach ($history as $h) {
            if (!empty($h['role']) && !empty($h['content'])) {
                $r = in_array($h['role'], ['user','assistant']) ? $h['role'] : 'user';
                $messages[] = ['role' => $r, 'content' => substr(strip_tags($h['content']), 0, 500)];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        // Panggil AI Service dengan konteks database
        $ai      = new AiService();
        $jawaban = $ai->chat($messages, $systemPrompt, $dbContext);

        // Guardrail Post-check: Pastikan AI tidak membocorkan kode
        if (preg_match('/```\s*(python|py|bash|sh|php|javascript|js|sql|c\+\+|java)/i', $jawaban) || 
            str_contains($jawaban, 'print("') || str_contains($jawaban, "print('")) {
            $jawaban = "🚫 **Akses Ditolak:**\n\nMaaf, saya tidak dapat menampilkan kode pemrograman. Saya adalah asisten Sistem Absensi yang terhubung langsung ke database sekolah.";
        }

        // Simpan percakapan ke database (tabel chat_history)
        $chatModel = $this->model('ChatHistoryModel');
        $chatModel->simpan($userId, $message, $jawaban);

        echo json_encode([
            'answer'   => $jawaban,
            'isMock'   => $ai->isMockMode(),
        ]);
        exit;
    }

    /**
     * Endpoint AJAX: GET /chatbot/history
     * Mengembalikan 20 pesan terakhir milik user yang login.
     */
    public function history() {
        header('Content-Type: application/json; charset=utf-8');

        // Wajib login
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            exit;
        }

        $userId    = (int)$_SESSION['user_id'];
        $chatModel = $this->model('ChatHistoryModel');
        $rows      = $chatModel->getRiwayat($userId, 20);

        $messages = [];
        foreach ($rows as $row) {
            $messages[] = ['role' => 'user',      'content' => $row['pertanyaan'], 'time' => $row['created_at']];
            $messages[] = ['role' => 'assistant', 'content' => $row['jawaban'],    'time' => $row['created_at']];
        }

        echo json_encode(['messages' => $messages]);
        exit;
    }

    /**
     * Endpoint AJAX: POST /chatbot/clear
     * Menghapus seluruh riwayat chat user.
     */
    public function clear() {
        header('Content-Type: application/json; charset=utf-8');

        // Wajib login
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method Not Allowed']);
            exit;
        }

        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            echo json_encode(['error' => 'CSRF token tidak valid.']);
            exit;
        }

        $userId    = (int)$_SESSION['user_id'];
        $chatModel = $this->model('ChatHistoryModel');
        $chatModel->hapusRiwayat($userId);

        echo json_encode(['success' => true]);
        exit;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function requireLogin(): void {
        if (!isset($_SESSION['user_id'])) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(401);
            echo json_encode(['error' => 'Silakan login terlebih dahulu.']);
            exit;
        }
    }

    /**
     * Bangun system prompt yang menjelaskan identitas pengguna dan menyediakan
     * seluruh data resmi dari database sistem absensi kepada AI.
     */
    private function buildSystemPrompt(string $role, string $username, string $dbContext): string {
        $roleLabels = [
            'admin'           => 'Admin',
            'guru'            => 'Guru',
            'kepala_sekolah'  => 'Kepala Sekolah',
            'wali_kelas'      => 'Wali Kelas',
            'siswa'           => 'Siswa',
        ];
        $roleLabel = $roleLabels[$role] ?? 'Pengguna';

        $rolePermissions = [
            'admin' => "Admin memiliki akses penuh: mengelola data siswa, guru, kelas, user, barcode, dan jadwal. Admin dapat melihat seluruh data dan statistik sekolah.",

            'guru' => "Guru dapat melihat jadwal mengajar, melakukan absensi barcode, melihat riwayat dan rekap absensi pribadinya, serta melihat data kelas binaan jika menjadi wali kelas.",

            'kepala_sekolah' => "Kepala Sekolah dapat melihat laporan dan statistik kehadiran seluruh siswa dan guru, serta mengekspor laporan ke CSV.",

            'wali_kelas' => "Wali Kelas dapat melihat data absensi siswa di kelas binaannya.",

            'siswa' => "Siswa dapat melihat data absensi pribadinya sendiri (status hadir, izin, sakit, alpa), jadwal kelasnya, barcode miliknya, dan wali kelasnya.",
        ];

        $permission = $rolePermissions[$role] ?? "Pengguna memiliki akses terbatas.";

        return <<<PROMPT
Kamu adalah AI Helpdesk resmi dari Sistem Absensi SMK NU WARGABINANGUN.
Kamu TERHUBUNG LANGSUNG DENGAN DATABASE SISTEM ABSENSI SEKOLAH (MySQL).
Tugas utamamu adalah menjawab pertanyaan pengguna seputar data di database sekolah secara akurat, lugas, dan to-the-point.

IDENTITAS PENGGUNA YANG SEDANG LOGIN:
- Username: {$username}
- Role: {$roleLabel}

BATASAN HAK AKSES SISTEM:
{$permission}

{$dbContext}

ATURAN WAJIB & SANGAT KETAT:
1. JAWAB HANYA SESUAI DENGAN APA YANG DITANYAKAN:
   - Berikan informasi atau data yang ditanyakan secara langsung, akurat, dan ringkas berdasarkan data resmi database di atas.
   - JIKA DITANYA JUMLAH ATAU BANYAKNYA (misalnya "berapa jumlah guru", "berapa siswa hadir", "total kelas", "ada berapa siswa"):
     HANYA SEBUTKAN ANGKA ATAU JUMLAHNYA SAJA (Contoh: "Total guru yang terdaftar di database sekolah: 12 guru.").
     DILARANG KERAS MENYEBUTKAN / MENAMPILKAN DAFTAR NAMA ORANG KECUALI PENGGUNA EKSPLISIT MEMINTA DAFTAR ("daftar guru", "siapa saja nama guru").
2. DILARANG KERAS MENAMBAHKAN KOMENTAR APAPUN:
   - JANGAN menambahkan kalimat pembuka basa-basi (seperti: "Tentu!", "Halo!", "Berikut informasinya:", "Baik, saya bantu").
   - JANGAN menambahkan kalimat penutup, penawaran bantuan, atau salam (seperti: "Ada yang bisa saya bantu lagi?", "Semoga membantu", "Semoga harimu menyenangkan", "Terima kasih", "Sampai jumpa").
   - JANGAN menambahkan komentar pribadi, saran, nasihat, atau kata-kata motivasi (seperti: "Pertahankan kehadiranmu", "Tetap semangat belajar", "Jangan lupa absen besok").
   - JANGAN menambahkan instruksi berlebihan jika tidak diminta (seperti: "Silakan hubungi wali kelas", "Arahkan barcode ke mesin scanner").
3. PERTANYAAN DATA ABSENSI / KEHADIRAN (Siswa & Guru):
   - Sajikan status absensi hari ini, jam absen, dan jumlah hadir/sakit/izin/alpa langsung sesuai data database tanpa komentar tambahan.
4. PERTANYAAN IDENTITAS DIRI ("siapa saya", "nama saya siapa", dll):
   - Jawab langsung data profil pengguna yang tercatat di database tanpa komentar tambahan.
5. JIKA DATA TIDAK ADA ATAU DI LUAR SISTEM:
   - Nyatakan secara singkat dan jelas: "Data tidak tercatat di database sekolah." tanpa komentar tambahan.
6. DILARANG KERAS MENULIS, MEMBUAT, ATAU MENAMPILKAN KODE PEMROGRAMAN APAPUN.
7. Format jawaban rapi dan jelas menggunakan Markdown.
8. KEPASTIAN KESESUAIAN JAWABAN:
   - Pastikan jawaban selalu tepat sasaran dan fokus hanya pada inti pertanyaan pengguna. Jangan menjawab melebar atau memberikan data yang tidak ditanyakan!
PROMPT;
    }
}
