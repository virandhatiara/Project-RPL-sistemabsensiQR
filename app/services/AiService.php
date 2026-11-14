<?php
/**
 * AiService — Mengintegrasikan NVIDIA NIM API (OpenAI Compatible)
 */
class AiService {

    private $apiKey;
    private $apiUrl = 'https://integrate.api.nvidia.com/v1/chat/completions';
    private $model = 'meta/llama-3.2-11b-vision-instruct';
    private $provider = 'nvidia';
    private $temperature = 0.7;
    private $maxTokens = 1024;
    private $useMock = false;

    public function __construct() {
        $loaded = false;
        try {
            if (class_exists('Database')) {
                $db = new Database();
                $db->query("SELECT * FROM ai_settings WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
                $setting = $db->single();
                if ($setting && !empty($setting['api_key'])) {
                    $this->provider    = $setting['provider'] ?? 'nvidia';
                    $this->apiKey      = trim($setting['api_key']);
                    $this->apiUrl      = !empty($setting['api_url']) ? trim($setting['api_url']) : 'https://integrate.api.nvidia.com/v1/chat/completions';
                    $this->model       = !empty($setting['model']) ? trim($setting['model']) : 'meta/llama-3.2-11b-vision-instruct';
                    $this->temperature = isset($setting['temperature']) ? (float)$setting['temperature'] : 0.7;
                    $this->maxTokens   = isset($setting['max_tokens']) ? (int)$setting['max_tokens'] : 1024;
                    $loaded = true;
                }
            }
        } catch (Exception $e) {
            error_log("[AiService] DB setting load error: " . $e->getMessage());
        }

        if (!$loaded) {
            $this->apiKey = defined('AI_API_KEY') && !empty(AI_API_KEY) ? AI_API_KEY : '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';
            $this->apiUrl = 'https://integrate.api.nvidia.com/v1/chat/completions';
            $this->model  = 'meta/llama-3.2-11b-vision-instruct';
        }

        if (empty($this->apiKey)) {
            $this->useMock = true;
        }
    }

    /**
     * Kirim pesan ke AI dan terima respons teks.
     *
     * @param array  $messages     Array riwayat percakapan: [['role'=>'user','content'=>'...'], ...]
     * @param string $systemPrompt Instruksi sistem yang mendefinisikan perilaku AI
     * @return string              Teks respons dari AI
     */
    public function chat(array $messages, string $systemPrompt, string $dbContext = ''): string {
        if ($this->useMock) {
            return $this->mockResponse($messages, $dbContext);
        }

        // Build OpenAI format messages array
        $formattedMessages = [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ]
        ];

        foreach ($messages as $msg) {
            $formattedMessages[] = [
                'role'  => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['content']
            ];
        }

        $payload = [
            'model'       => $this->model,
            'messages'    => $formattedMessages,
            'temperature' => 0.2,
            'max_tokens'  => 1024,
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
                'Accept: application/json'
            ],
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            // Fallback cerdas ke data database lokal jika koneksi API sedang gangguan
            error_log("[AiService] Fallback to DB-aware mock: {$curlError} HTTP: {$httpCode}");
            return $this->mockResponse($messages, $dbContext);
        }

        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? $this->mockResponse($messages, $dbContext);
    }

    /**
     * Respon cerdas berbasis data resmi database jika API eksternal offline atau demo.
     */
    private function mockResponse(array $messages, string $dbContext = ''): string {
        $lastMessage = strtolower(end($messages)['content'] ?? '');

        // 1. Blokir permintaan kode pemrograman (termasuk Python)
        if (str_contains($lastMessage, 'python') || str_contains($lastMessage, 'coding') || 
            str_contains($lastMessage, 'kode program') || str_contains($lastMessage, 'script') ||
            str_contains($lastMessage, 'buatkan kode') || str_contains($lastMessage, 'syntax')) {
            return "🚫 **Akses Ditolak:**\n\nMaaf, saya tidak dapat membuat atau menampilkan kode pemrograman apapun (termasuk Python). Saya adalah asisten sistem absensi yang terhubung ke database sekolah.";
        }

        // 2. Blokir pertanyaan umum di luar konteks sistem absensi
        $offTopicKeywords = ['presiden', 'cuaca', 'resep', 'dongeng', 'artis', 'game', 'film', 'cerpen', 'puisi', 'politik', 'olahraga luar', 'sejarah dunia'];
        foreach ($offTopicKeywords as $otk) {
            if (str_contains($lastMessage, $otk)) {
                return "Data tidak tercatat di database sekolah. Pertanyaan di luar konteks sistem absensi tidak didukung.";
            }
        }

        // 3. Identitas Diri ("siapa saya", "saya siapa", "nama saya", "profil saya", dll)
        $cleanMsg = preg_replace('/[^\w\s]/', '', $lastMessage);
        $cleanMsg = preg_replace('/\s+/', ' ', $cleanMsg);
        $identityPatterns = [
            '/\b(siapa|siapakah)\s*(sih|ya|ini|sebenarnya)?\s*(saya|aku|diriku|gua|gw)\b/i',
            '/\b(saya|aku|diriku|gua|gw)\s*(ini|sih|ya|sebenarnya)?\s*(siapa|siapakah)\b/i',
            '/\b(nama\s+lengkap\s+(saya|aku|diriku|gua|gw)|nama\s+(saya|aku|diriku|gua|gw)|namaku)\s*(siapa|apa)\b/i',
            '/\b(siapa|apa)\s+(nama\s+lengkap\s+(saya|aku|diriku|gua|gw)|nama\s+(saya|aku|diriku|gua|gw)|namaku)\b/i',
            '/\b(profil|profile|identitas|data\s*diri|biodata)\s+(saya|aku|diriku|gua|gw|ku)\b/i',
            '/\b(siapa|siapakah)\s+(akun|username)\s+(saya|aku|diriku|gua|gw)\b/i',
        ];

        $isIdentity = false;
        foreach ($identityPatterns as $p) {
            if (preg_match($p, $cleanMsg)) {
                $isIdentity = true;
                break;
            }
        }

        if ($isIdentity) {
            if (preg_match('/\[DATA DIRI PENGGUNA YANG LOGIN\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "👤 **Berdasarkan data profil resmi di database sekolah, identitas Anda adalah:**\n\n" . trim($m[1]);
            }
            if (preg_match('/\[PROFIL DATA DIRI[^\n\r]*\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "👤 **Berdasarkan data resmi di database sekolah, identitas Anda adalah:**\n\n" . trim($m[1]);
            }
            if (preg_match('/Akun Login Anda:\s*([^\n\r]+)/i', $dbContext, $m)) {
                return "👤 Anda login sebagai **" . trim($m[1]) . "** di Sistem Absensi SMK NU WARGABINANGUN.";
            }
        }

        // 4. Jawab pertanyaan JUMLAH / TOTAL / STATISTIK (Dahulukan sebelum pengecekan daftar nama!)
        // 4a. Jumlah Guru
        if (preg_match('/\b(berapa\s+(jumlah|total|banyak\s+(jumlah\s+)?)?guru|jumlah\s+guru|total\s+guru|ada\s+berapa\s+(banyak\s+)?guru|banyaknya\s+guru|guru\s+(ada\s+)?berapa(\s+banyak)?)\b/i', $lastMessage) &&
            !preg_match('/\b(hadir|masuk|alpa|alpha|sakit|izin|terlambat)\b/i', $lastMessage)) {
            if (preg_match('/Total Guru Terdaftar:\s*([0-9]+)\s*guru/i', $dbContext, $m)) {
                return "👨‍🏫 Total guru yang terdaftar di database sekolah: **{$m[1]} guru**.";
            }
        }

        // 4b. Jumlah Siswa
        if (preg_match('/\b(berapa\s+(jumlah|total|banyak\s+(jumlah\s+)?)?(siswa|murid)|jumlah\s+(siswa|murid)|total\s+(siswa|murid)|ada\s+berapa\s+(banyak\s+)?(siswa|murid)|banyaknya\s+(siswa|murid)|(siswa|murid)\s+(ada\s+)?berapa(\s+banyak)?)\b/i', $lastMessage) &&
            !preg_match('/\b(hadir|masuk|alpa|alpha|sakit|izin)\b/i', $lastMessage)) {
            if (preg_match('/Total Siswa Terdaftar:\s*([0-9]+)\s*siswa/i', $dbContext, $m)) {
                return "👥 Total siswa yang terdaftar di database sekolah: **{$m[1]} siswa**.";
            }
        }

        // 4c. Jumlah Kelas / Rombel
        if (preg_match('/\b(berapa\s+(jumlah|total|banyak\s+(jumlah\s+)?)?(kelas|rombel)|jumlah\s+(kelas|rombel)|total\s+(kelas|rombel)|ada\s+berapa\s+(banyak\s+)?(kelas|rombel)|banyaknya\s+(kelas|rombel)|(kelas|rombel)\s+(ada\s+)?berapa(\s+banyak)?)\b/i', $lastMessage)) {
            if (preg_match('/Total Rombongan Belajar \(Kelas\):\s*([0-9]+)\s*kelas/i', $dbContext, $m)) {
                return "🏫 Total rombel (kelas) yang terdaftar di database sekolah: **{$m[1]} kelas**.";
            }
        }

        // 4d. Jumlah User / Pengguna
        if (preg_match('/\b(berapa\s+(jumlah|total|banyak\s+(jumlah\s+)?)?(user|pengguna|akun)|jumlah\s+(user|pengguna|akun)|total\s+(user|pengguna|akun)|ada\s+berapa\s+(banyak\s+)?(user|pengguna|akun)|banyaknya\s+(user|pengguna|akun)|(user|pengguna|akun)\s+(ada\s+)?berapa(\s+banyak)?)\b/i', $lastMessage)) {
            if (preg_match('/Total Akun Pengguna \(Users\):\s*([0-9]+)\s*akun/i', $dbContext, $m)) {
                return "👤 Total akun pengguna terdaftar di database sistem: **{$m[1]} user**.";
            }
        }

        // 4e. Jumlah Siswa Berdasarkan Status Hari Ini (Hadir, Sakit, Izin, Alpa, Terlambat, Belum Absen)
        if (preg_match('/\b(berapa\s+(jumlah\s+)?siswa\s+(yang\s+)?(hadir|masuk)|(jumlah|total)\s+siswa\s+(yang\s+)?(hadir|masuk)|siswa\s+(yang\s+)?(hadir|masuk)\s+(ada\s+)?berapa)\b/i', $lastMessage)) {
            if (preg_match('/Siswa Hadir:\s*([0-9]+)/i', $dbContext, $m)) {
                return "👥 Total siswa yang hadir hari ini: **{$m[1]} siswa**.";
            }
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?siswa\s+(yang\s+)?(alpa|alpha)|(jumlah|total)\s+siswa\s+(yang\s+)?(alpa|alpha)|siswa\s+(yang\s+)?(alpa|alpha)\s+(ada\s+)?berapa)\b/i', $lastMessage)) {
            if (preg_match('/Alpa:\s*([0-9]+)/i', $dbContext, $m)) {
                return "❌ Total siswa yang alpa hari ini: **{$m[1]} siswa**.";
            }
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?siswa\s+(yang\s+)?sakit|(jumlah|total)\s+siswa\s+(yang\s+)?sakit|siswa\s+(yang\s+)?sakit\s+(ada\s+)?berapa)\b/i', $lastMessage)) {
            if (preg_match('/Sakit:\s*([0-9]+)/i', $dbContext, $m)) {
                return "🤒 Total siswa yang sakit hari ini: **{$m[1]} siswa**.";
            }
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?siswa\s+(yang\s+)?izin|(jumlah|total)\s+siswa\s+(yang\s+)?izin|siswa\s+(yang\s+)?izin\s+(ada\s+)?berapa)\b/i', $lastMessage)) {
            if (preg_match('/Izin:\s*([0-9]+)/i', $dbContext, $m)) {
                return "✉️ Total siswa yang izin hari ini: **{$m[1]} siswa**.";
            }
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?siswa\s+(yang\s+)?belum\s+absen|(jumlah|total)\s+siswa\s+(yang\s+)?belum\s+absen|siswa\s+(yang\s+)?belum\s+absen\s+(ada\s+)?berapa)\b/i', $lastMessage)) {
            if (preg_match('/\[DAFTAR SISWA BELUM MELAKUKAN ABSENSI HARI INI[^\n\r]*\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                $countBelum = preg_match_all('/^\s*\*\s+/m', $m[1]);
                return "⏳ Total siswa yang belum absen hari ini: **{$countBelum} siswa**.";
            }
        }

        // 4f. Jumlah Guru Berdasarkan Status Hari Ini (Hadir, Sakit, Izin, Alpa, Terlambat)
        if (preg_match('/\b(berapa\s+(jumlah\s+)?guru\s+(yang\s+)?(hadir|masuk)|(jumlah|total)\s+guru\s+(yang\s+)?(hadir|masuk)|guru\s+(yang\s+)?(hadir|masuk)\s+(ada\s+)?berapa)\b/i', $lastMessage)) {
            if (preg_match('/Guru Hadir[^:]*:\s*([0-9]+)/i', $dbContext, $m)) {
                return "👨‍🏫 Total guru yang hadir hari ini: **{$m[1]} guru**.";
            }
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?guru\s+(yang\s+)?(alpa|alpha)|(jumlah|total)\s+guru\s+(yang\s+)?(alpa|alpha)|guru\s+(yang\s+)?(alpa|alpha)\s+(ada\s+)?berapa)\b/i', $lastMessage)) {
            if (preg_match('/Guru[^\n\r]*Alpa:\s*([0-9]+)/i', $dbContext, $m)) {
                return "❌ Total guru yang alpa hari ini: **{$m[1]} guru**.";
            }
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?guru\s+(yang\s+)?sakit|(jumlah|total)\s+guru\s+(yang\s+)?sakit|guru\s+(yang\s+)?sakit\s+(ada\s+)?berapa)\b/i', $lastMessage)) {
            if (preg_match('/Guru[^\n\r]*Sakit:\s*([0-9]+)/i', $dbContext, $m)) {
                return "🤒 Total guru yang sakit hari ini: **{$m[1]} guru**.";
            }
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?guru\s+(yang\s+)?izin|(jumlah|total)\s+guru\s+(yang\s+)?izin|guru\s+(yang\s+)?izin\s+(ada\s+)?berapa)\b/i', $lastMessage)) {
            if (preg_match('/Guru[^\n\r]*Izin:\s*([0-9]+)/i', $dbContext, $m)) {
                return "✉️ Total guru yang izin hari ini: **{$m[1]} guru**.";
            }
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?guru\s+(yang\s+)?terlambat|(jumlah|total)\s+guru\s+(yang\s+)?terlambat|guru\s+(yang\s+)?terlambat\s+(ada\s+)?berapa)\b/i', $lastMessage)) {
            if (preg_match('/Guru[^\n\r]*Terlambat:\s*([0-9]+)/i', $dbContext, $m)) {
                return "⚠️ Total guru yang terlambat hari ini: **{$m[1]} guru**.";
            }
        }

        // 5. Jawab pertanyaan DAFTAR NAMA EKSPLISIT (HANYA jika tidak menanyakan jumlah/berapa/total)
        // 5a. Daftar Guru
        if (preg_match('/\b(daftar\s+guru|siapa\s+(saja\s+)?guru|sebutkan\s+(nama\s+)?guru|nama\s+nama\s+guru|seluruh\s+guru|semua\s+guru)\b/i', $lastMessage) &&
            !preg_match('/\b(berapa|jumlah|total|hitungan)\b/i', $lastMessage)) {
            if (preg_match('/\[HASIL PENCARIAN GURU DARI DATABASE\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "👨‍🏫 **Hasil Pencarian Guru di Database:**\n\n" . trim($m[1]);
            }
            if (preg_match('/\[DAFTAR SELURUH GURU DI DATABASE SEKOLAH\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "👨‍🏫 **Daftar Seluruh Guru di Database Sekolah:**\n\n" . trim($m[1]);
            }
        }

        // 5b. Daftar Kelas & Wali Kelas
        if (preg_match('/\b(daftar\s+kelas|apa\s+(saja\s+)?kelas|sebutkan\s+kelas|seluruh\s+kelas|semua\s+kelas)\b/i', $lastMessage) &&
            !preg_match('/\b(berapa|jumlah|total|hitungan)\b/i', $lastMessage)) {
            if (preg_match('/\[INFORMASI KELAS[^\n\r]*\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "🏫 **Informasi Kelas dari Database:**\n\n" . trim($m[1]);
            }
            if (preg_match('/\[DAFTAR SELURUH KELAS DI SEKOLAH DARI DATABASE\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "🏫 **Daftar Seluruh Kelas & Wali Kelas di Database:**\n\n" . trim($m[1]);
            }
        }

        // 5c. Daftar Siswa
        if (preg_match('/\b(daftar\s+siswa|siapa\s+(saja\s+)?siswa|sebutkan\s+(nama\s+)?siswa|nama\s+nama\s+siswa|seluruh\s+siswa|semua\s+siswa)\b/i', $lastMessage) &&
            !preg_match('/\b(berapa|jumlah|total|hitungan)\b/i', $lastMessage)) {
            if (preg_match('/\[DAFTAR SISWA KELAS[^\n\r]*\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "👥 **Daftar Siswa di Kelas Tersebut:**\n\n" . trim($m[1]);
            }
            if (preg_match('/\[DAFTAR SELURUH SISWA PER KELAS\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "👥 **Daftar Seluruh Siswa per Kelas di Database:**\n\n" . trim($m[1]);
            }
        }

        // 6. Jawab pertanyaan Jadwal Pelajaran
        if (str_contains($lastMessage, 'jadwal') || str_contains($lastMessage, 'pelajaran') || str_contains($lastMessage, 'mengajar')) {
            if (preg_match('/\[DATA JADWAL PELAJARAN DARI DATABASE\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "📅 **Jadwal Pelajaran dari Database:**\n\n" . trim($m[1]);
            }
        }

        // 7. Jawab pertanyaan Barcode
        if (str_contains($lastMessage, 'barcode') || preg_match('/(BC-|RFID-)/i', $lastMessage)) {
            if (preg_match('/\[HASIL PENCARIAN BARCODE\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "🪪 **Data Pemilik Barcode dari Database:**\n\n" . trim($m[1]);
            }
        }

        // 8. Jawab pertanyaan Kepala Sekolah & Admin
        if (str_contains($lastMessage, 'kepala sekolah') || str_contains($lastMessage, 'kepsek')) {
            if (preg_match('/Kepala Sekolah Tercatat:\s*([^\n\r]+)/i', $dbContext, $m)) {
                return "🎓 Berdasarkan data resmi di database sekolah, akun Kepala Sekolah yang terdaftar adalah: **" . trim($m[1]) . "**.";
            }
        }

        // 9. Jawab pertanyaan Status Absensi (Belum Absen, Sakit, Izin, Alpa, Terlambat)
        if (str_contains($lastMessage, 'belum absen') || str_contains($lastMessage, 'tidak hadir')) {
            if (preg_match('/\[DAFTAR SISWA BELUM MELAKUKAN ABSENSI HARI INI[^\n\r]*\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "📋 **Daftar Siswa Belum Absen Hari Ini:**\n\n" . trim($m[1]);
            }
        }

        if (preg_match('/\[DAFTAR SISWA DENGAN STATUS[^\n\r]*\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
            return "📋 " . trim($m[0]);
        }

        if (str_contains($lastMessage, 'status') || str_contains($lastMessage, 'sudah absen') || str_contains($lastMessage, 'kehadiran saya') || str_contains($lastMessage, 'apakah saya')) {
            if (preg_match('/\[STATUS KEHADIRAN PRIBADI HARI INI\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "📋 **Status Kehadiran Anda Hari Ini dari Database:**\n\n" . trim($m[1]);
            }
            if (preg_match('/Status Absensi Hari Ini:\s*([^\n\r]+)/i', $dbContext, $m)) {
                $status = trim($m[1]);
                return "ℹ️ Status kehadiran Anda di database untuk hari ini:\n\n**{$status}**";
            }
        }

        if (str_contains($lastMessage, 'rekap') || str_contains($lastMessage, 'total kehadiran') || str_contains($lastMessage, 'berapa kali')) {
            if (preg_match('/\[REKAPITULASI KEHADIRAN PRIBADI[^\n\r]*\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "📊 **Rekapitulasi Kehadiran Pribadi Anda dari Database:**\n\n" . trim($m[1]);
            }
            if (preg_match('/\[REKAPITULASI KEHADIRAN HARI INI[^\n\r]*\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "📋 **Rekapitulasi Kehadiran Hari Ini di Database:**\n\n" . trim($m[1]);
            }
        }

        if (str_contains($lastMessage, 'riwayat') || str_contains($lastMessage, 'catatan absen') || str_contains($lastMessage, 'log absen') || str_contains($lastMessage, 'daftar hadir')) {
            if (preg_match('/\[RIWAYAT KEHADIRAN PRIBADI[^\n\r]*\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "📜 **Riwayat Kehadiran Pribadi Anda dari Database:**\n\n" . trim($m[1]);
            }
        }

        if (str_contains($lastMessage, 'jadwal') || str_contains($lastMessage, 'pelajaran') || str_contains($lastMessage, 'mengajar')) {
            if (preg_match('/\[JADWAL PELAJARAN KELAS HARI INI[^\n\r]*\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "📅 **Jadwal Pelajaran Kelas Hari Ini dari Database:**\n\n" . trim($m[1]);
            }
            if (preg_match('/\[JADWAL MENGAJAR HARI INI[^\n\r]*\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "📅 **Jadwal Mengajar Anda Hari Ini dari Database:**\n\n" . trim($m[1]);
            }
            if (preg_match('/\[DATA JADWAL PELAJARAN DARI DATABASE\]([\s\S]*?)(\[|$)/i', $dbContext, $m)) {
                return "📅 **Data Jadwal Pelajaran dari Database:**\n\n" . trim($m[1]);
            }
        }

        $rules = [
            ['keywords' => ['barcode','kartu','scan siswa','tidak terbaca'],
             'answer'   => "🪪 **Cara Absensi Barcode Siswa:**\n\n1. Pastikan scanner Barcode menyala\n2. Arahkan Barcode Anda ke scanner\n3. Layar akan menampilkan nama & status absensi\n\n**Jika Barcode tidak terbaca:**\n- Pastikan tingkat kecerahan layar HP cukup (jika menggunakan HP)\n- Pastikan gambar Barcode tidak buram atau rusak\n- Hubungi Admin untuk mengecek pendaftaran Barcode Anda"],

            ['keywords' => ['barcode','scan guru','masuk guru','pulang guru'],
             'answer'   => "📊 **Cara Absensi Barcode Guru:**\n\n**Scan Masuk (pagi):**\n1. Arahkan barcode ID Anda ke scanner\n2. Sistem otomatis mencatat jam masuk\n\n**Scan Pulang (sore):**\n1. Scan kembali barcode yang sama\n2. Sistem mencatat jam pulang (minimal 30 menit setelah scan masuk)\n\n⚠️ *Jika sudah scan masuk & pulang, sistem tidak menerima scan tambahan hari itu.*"],

            ['keywords' => ['jadwal','mengajar','pelajaran','jam'],
             'answer'   => "📅 **Jadwal Pelajaran / Mengajar:**\n\nJadwal telah terintegrasi dengan database sekolah. Anda dapat melihat jadwal lengkap di dashboard akun Anda masing-masing atau menanyakan jadwal hari tertentu."],

            ['keywords' => ['laporan','export','cetak','print','csv'],
             'answer'   => "📊 **Melihat & Mengekspor Laporan (Kepala Sekolah & Admin):**\n\n1. Buka menu **Laporan** di sidebar\n2. Pilih jenis laporan: Siswa atau Guru\n3. Filter berdasarkan tanggal atau kelas\n4. Klik tombol **Export CSV** untuk mengunduh laporan real-time dari database."],

            ['keywords' => ['lupa password','reset password','ganti password','tidak bisa login'],
             'answer'   => "🔑 **Lupa Password:**\n\n1. Hubungi **Admin** sekolah\n2. Admin akan mereset password akun Anda di menu *Data Master → Data User*\n3. Gunakan password sementara yang diberikan lalu ubah demi keamanan."],
        ];

        foreach ($rules as $rule) {
            foreach ($rule['keywords'] as $kw) {
                if (str_contains($lastMessage, $kw)) {
                    return $rule['answer'];
                }
            }
        }

        // Default response terhubung database
        return "🤖 **Layanan Data Sistem Absensi:**\n\nSilakan tanyakan data absensi, rekapitulasi kehadiran, jadwal pelajaran/mengajar, rombel kelas, atau informasi profil dari database sekolah.";
    }

    public function isMockMode(): bool {
        return $this->useMock;
    }
}
