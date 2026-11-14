<?php
/**
 * ChatbotModel — Menghubungkan AI Chatbot dengan Database Sistem Absensi.
 * Mengambil data real-time dari MySQL (profil pengguna, absensi, jadwal, data master, statistik).
 */
class ChatbotModel {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /**
     * Konversi status integer siswa menjadi teks Bahasa Indonesia
     * 0 = Hadir, 1 = Sakit, 2 = Izin, 3 = Alpha
     */
    public function formatStatusSiswa($status): string {
        if ($status === null || $status === '') return 'Belum Absen';
        $st = (int)$status;
        switch ($st) {
            case 0: return 'Hadir';
            case 1: return 'Sakit';
            case 2: return 'Izin';
            case 3: return 'Alpha';
            default: return 'Hadir';
        }
    }

    /**
     * Konversi status enum guru menjadi teks rapi
     */
    public function formatStatusGuru($status): string {
        if ($status === null || $status === '') return 'Belum Absen';
        $st = strtolower(trim($status));
        switch ($st) {
            case 'hadir': return 'Hadir (Tepat Waktu)';
            case 'terlambat': return 'Hadir (Terlambat)';
            case 'sakit': return 'Sakit';
            case 'izin': return 'Izin';
            case 'alpa':
            case 'alpha': return 'Alpha';
            default: return ucfirst($st);
        }
    }

    /**
     * Format tanggal dalam bahasa Indonesia (e.g. 17 Oktober 2026)
     */
    public function formatTanggalIndo(string $dateStr): string {
        $bulanIndo = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];
        $timestamp = strtotime($dateStr);
        $d = date('j', $timestamp);
        $m = (int)date('n', $timestamp);
        $y = date('Y', $timestamp);
        return "{$d} " . ($bulanIndo[$m] ?? '') . " {$y}";
    }

    /**
     * Memeriksa apakah pesan pengguna menanyakan identitas diri
     * seperti "siapa saya", "saya siapa", "nama saya siapa", "profil saya", dsb.
     */
    public function isIdentityQuestion(string $message): bool {
        $clean = preg_replace('/[^\w\s]/', '', strtolower(trim($message)));
        $clean = preg_replace('/\s+/', ' ', $clean);

        $patterns = [
            '/\b(siapa|siapakah)\s*(sih|ya|ini|sebenarnya)?\s*(saya|aku|diriku|gua|gw)\b/i',
            '/\b(saya|aku|diriku|gua|gw)\s*(ini|sih|ya|sebenarnya)?\s*(siapa|siapakah)\b/i',
            '/\b(nama\s+lengkap\s+(saya|aku|diriku|gua|gw)|nama\s+(saya|aku|diriku|gua|gw)|namaku)\s*(siapa|apa)\b/i',
            '/\b(siapa|apa)\s+(nama\s+lengkap\s+(saya|aku|diriku|gua|gw)|nama\s+(saya|aku|diriku|gua|gw)|namaku)\b/i',
            '/\b(profil|profile|identitas|data\s*diri|biodata)\s+(saya|aku|diriku|gua|gw|ku)\b/i',
            '/\b(siapa|siapakah)\s+(akun|username)\s+(saya|aku|diriku|gua|gw)\b/i',
        ];

        foreach ($patterns as $p) {
            if (preg_match($p, $clean)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Menghasilkan jawaban identitas diri lengkap dari database secara akurat
     */
    public function getIdentityResponse(int $userId, string $role, string $username): string {
        $tanggal = date('Y-m-d');
        $hari    = $this->getHariIndo($tanggal);
        $tglIndo = $this->formatTanggalIndo($tanggal);

        switch ($role) {
            case 'siswa':
                $this->db->query("
                    SELECT s.*, k.nama_kelas, COALESCE(g.nama, 'Belum ditentukan') as nama_wali
                    FROM siswa s
                    LEFT JOIN kelas k ON s.kelas_id = k.id
                    LEFT JOIN guru g ON k.wali_kelas_id = g.id
                    WHERE s.user_id = :user_id
                ");
                $this->db->bind('user_id', $userId);
                $siswa = $this->db->single();

                if ($siswa) {
                    // Status absensi hari ini
                    $this->db->query("
                        SELECT jam, status 
                        FROM absensi_siswa 
                        WHERE siswa_id = :siswa_id AND tanggal = :tanggal
                        ORDER BY id DESC LIMIT 1
                    ");
                    $this->db->bind('siswa_id', $siswa['id']);
                    $this->db->bind('tanggal', $tanggal);
                    $absenHariIni = $this->db->single();

                    $stLabel = $absenHariIni ? $this->formatStatusSiswa($absenHariIni['status']) : 'Belum Absen';
                    $statusText = $absenHariIni 
                        ? "Sudah absen ({$stLabel} jam " . substr($absenHariIni['jam'], 0, 5) . " WIB)"
                        : "Belum melakukan absensi hari ini";

                    $barcodeText = !empty($siswa['barcode_id']) ? $siswa['barcode_id'] : 'Belum di-generate';

                    return "👤 **Identitas Diri (Database):**\n\n" .
                           "- **Nama Lengkap:** " . $siswa['nama'] . "\n" .
                           "- **NIS:** " . $siswa['nis'] . "\n" .
                           "- **Kelas:** " . ($siswa['nama_kelas'] ?? 'Belum ditentukan') . "\n" .
                           "- **Wali Kelas:** " . $siswa['nama_wali'] . "\n" .
                           "- **Peran (Role):** Siswa\n" .
                           "- **Barcode ID:** " . $barcodeText . "\n" .
                           "- **Status Absensi Hari Ini (" . $hari . ", " . $tglIndo . "):** " . $statusText;
                }
                break;

            case 'guru':
                $this->db->query("
                    SELECT g.*, u.username
                    FROM guru g
                    JOIN users u ON g.user_id = u.id
                    WHERE g.user_id = :user_id
                ");
                $this->db->bind('user_id', $userId);
                $guru = $this->db->single();

                if ($guru) {
                    $this->db->query("SELECT nama_kelas FROM kelas WHERE wali_kelas_id = :guru_id");
                    $this->db->bind('guru_id', $guru['id']);
                    $kelasWali = $this->db->single();
                    $tugasTambahan = $kelasWali ? "Wali Kelas " . $kelasWali['nama_kelas'] : "Guru Mata Pelajaran";

                    // Absensi hari ini
                    $this->db->query("
                        SELECT jam_masuk, jam_pulang, status 
                        FROM absensi_guru 
                        WHERE guru_id = :guru_id AND tanggal = :tanggal
                    ");
                    $this->db->bind('guru_id', $guru['id']);
                    $this->db->bind('tanggal', $tanggal);
                    $absenHariIni = $this->db->single();

                    $statusText = $absenHariIni 
                        ? "Sudah absen (" . $this->formatStatusGuru($absenHariIni['status']) . " masuk jam " . substr($absenHariIni['jam_masuk'], 0, 5) . " WIB" . ($absenHariIni['jam_pulang'] ? " & pulang jam " . substr($absenHariIni['jam_pulang'], 0, 5) : "") . ")"
                        : "Belum melakukan absensi hari ini";

                    $barcodeText = !empty($guru['barcode_id']) ? $guru['barcode_id'] : 'Belum di-generate';

                    return "👨‍🏫 **Identitas Diri (Database):**\n\n" .
                           "- **Nama Lengkap:** " . $guru['nama'] . "\n" .
                           "- **NIP:** " . $guru['nip'] . "\n" .
                           "- **Peran (Role):** Guru\n" .
                           "- **Tugas:** " . $tugasTambahan . "\n" .
                           "- **Barcode ID:** " . $barcodeText . "\n" .
                           "- **Status Absensi Hari Ini (" . $hari . ", " . $tglIndo . "):** " . $statusText;
                }
                break;

            case 'wali_kelas':
                $this->db->query("
                    SELECT g.*, k.nama_kelas, (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa
                    FROM guru g
                    JOIN kelas k ON k.wali_kelas_id = g.id
                    WHERE g.user_id = :user_id
                ");
                $this->db->bind('user_id', $userId);
                $wk = $this->db->single();

                if ($wk) {
                    return "🏫 **Identitas Diri (Database):**\n\n" .
                           "- **Nama Lengkap:** " . $wk['nama'] . "\n" .
                           "- **NIP:** " . $wk['nip'] . "\n" .
                           "- **Peran (Role):** Wali Kelas " . $wk['nama_kelas'] . "\n" .
                           "- **Jumlah Siswa Kelas Binaan:** " . (int)$wk['total_siswa'] . " siswa\n" .
                           "- **Barcode ID:** " . ($wk['barcode_id'] ?? '-');
                }
                break;

            case 'kepala_sekolah':
                return "🎓 **Identitas Diri (Database):**\n\n" .
                       "- **Akun Pengguna:** " . $username . "\n" .
                       "- **Jabatan / Peran:** Kepala Sekolah\n" .
                       "- **Wewenang:** Memantau laporan dan statistik kehadiran seluruh siswa dan guru sekolah.";

            case 'admin':
                return "🛡️ **Identitas Diri (Database):**\n\n" .
                       "- **Akun Pengguna:** " . $username . "\n" .
                       "- **Jabatan / Peran:** Administrator Sistem Absensi\n" .
                       "- **Wewenang:** Akses penuh pengelolaan data master (Siswa, Guru, Kelas, Jadwal, User, Barcode).";
        }

        return "👤 Anda login sebagai **" . $username . "** dengan peran **" . strtoupper($role) . "** di Sistem Absensi SMK NU WARGABINANGUN.";
    }

    /**
     * Nama hari dalam Bahasa Indonesia
     */
    public function getHariIndo($dateStr = null): string {
        $timestamp = $dateStr ? strtotime($dateStr) : time();
        $hariInggris = date('l', $timestamp);
        $map = [
            'Sunday'    => 'Minggu',
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu'
        ];
        return $map[$hariInggris] ?? 'Senin';
    }

    /**
     * Menangani pertanyaan langsung seputar absensi pribadi, rekapitulasi,
     * riwayat absensi, dan jadwal untuk role siswa dan guru langsung dari database.
     * Mengembalikan null jika pertanyaan bukan salah satu pertanyaan absensi langsung.
     */
    public function getDirectAttendanceResponse(int $userId, string $role, string $username, string $message): ?string {
        $clean = preg_replace('/[^\w\s]/', '', strtolower(trim($message)));
        $clean = preg_replace('/\s+/', ' ', $clean);
        $tanggal = date('Y-m-d');
        $hari    = $this->getHariIndo($tanggal);
        $tglIndo = $this->formatTanggalIndo($tanggal);

        // 0. Pertanyaan Total / Jumlah Master Data (Guru, Siswa, Kelas, User)
        if (preg_match('/\b(berapa\s+(jumlah|total|banyak\s+(jumlah\s+)?)?guru|jumlah\s+guru|total\s+guru|ada\s+berapa\s+(banyak\s+)?guru|banyaknya\s+guru|guru\s+(ada\s+)?berapa(\s+banyak)?)\b/i', $clean) && !preg_match('/\b(hadir|masuk|alpa|alpha|sakit|izin|terlambat)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as total FROM guru");
            $tot = (int)($this->db->single()['total'] ?? 0);
            return "👨‍🏫 Total guru yang terdaftar di database sekolah: **{$tot} guru**.";
        }

        if (preg_match('/\b(berapa\s+(jumlah|total|banyak\s+(jumlah\s+)?)?(siswa|murid)|jumlah\s+(siswa|murid)|total\s+(siswa|murid)|ada\s+berapa\s+(banyak\s+)?(siswa|murid)|banyaknya\s+(siswa|murid)|(siswa|murid)\s+(ada\s+)?berapa(\s+banyak)?)\b/i', $clean) && !preg_match('/\b(hadir|masuk|alpa|alpha|sakit|izin)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as total FROM siswa");
            $tot = (int)($this->db->single()['total'] ?? 0);
            return "👥 Total siswa yang terdaftar di database sekolah: **{$tot} siswa**.";
        }

        if (preg_match('/\b(berapa\s+(jumlah|total|banyak\s+(jumlah\s+)?)?(kelas|rombel)|jumlah\s+(kelas|rombel)|total\s+(kelas|rombel)|ada\s+berapa\s+(banyak\s+)?(kelas|rombel)|banyaknya\s+(kelas|rombel)|(kelas|rombel)\s+(ada\s+)?berapa(\s+banyak)?)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as total FROM kelas");
            $tot = (int)($this->db->single()['total'] ?? 0);
            return "🏫 Total rombel (kelas) yang terdaftar di database sekolah: **{$tot} kelas**.";
        }

        if (preg_match('/\b(berapa\s+(jumlah|total|banyak\s+(jumlah\s+)?)?(user|pengguna|akun)|jumlah\s+(user|pengguna|akun)|total\s+(user|pengguna|akun)|ada\s+berapa\s+(banyak\s+)?(user|pengguna|akun)|banyaknya\s+(user|pengguna|akun)|(user|pengguna|akun)\s+(ada\s+)?berapa(\s+banyak)?)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as total FROM users");
            $tot = (int)($this->db->single()['total'] ?? 0);
            return "👤 Total akun pengguna terdaftar di database sistem: **{$tot} user**.";
        }

        // Pertanyaan Spesifik Jumlah Absensi Guru / Siswa Hari Ini
        if (preg_match('/\b(berapa\s+(jumlah\s+)?siswa\s+(yang\s+)?(hadir|masuk)|(jumlah|total)\s+siswa\s+(yang\s+)?(hadir|masuk)|siswa\s+(yang\s+)?(hadir|masuk)\s+(ada\s+)?berapa)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as jml FROM absensi_siswa WHERE tanggal = :t AND status = 0");
            $this->db->bind('t', $tanggal);
            $cnt = (int)$this->db->single()['jml'];
            return "👥 Total siswa yang hadir hari ini ({$hari}, {$tglIndo}): **{$cnt} siswa**.";
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?siswa\s+(yang\s+)?(alpa|alpha)|(jumlah|total)\s+siswa\s+(yang\s+)?(alpa|alpha)|siswa\s+(yang\s+)?(alpa|alpha)\s+(ada\s+)?berapa)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as jml FROM absensi_siswa WHERE tanggal = :t AND status = 3");
            $this->db->bind('t', $tanggal);
            $cnt = (int)$this->db->single()['jml'];
            return "❌ Total siswa yang alpa hari ini ({$hari}, {$tglIndo}): **{$cnt} siswa**.";
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?siswa\s+(yang\s+)?sakit|(jumlah|total)\s+siswa\s+(yang\s+)?sakit|siswa\s+(yang\s+)?sakit\s+(ada\s+)?berapa)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as jml FROM absensi_siswa WHERE tanggal = :t AND status = 1");
            $this->db->bind('t', $tanggal);
            $cnt = (int)$this->db->single()['jml'];
            return "🤒 Total siswa yang sakit hari ini ({$hari}, {$tglIndo}): **{$cnt} siswa**.";
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?siswa\s+(yang\s+)?izin|(jumlah|total)\s+siswa\s+(yang\s+)?izin|siswa\s+(yang\s+)?izin\s+(ada\s+)?berapa)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as jml FROM absensi_siswa WHERE tanggal = :t AND status = 2");
            $this->db->bind('t', $tanggal);
            $cnt = (int)$this->db->single()['jml'];
            return "✉️ Total siswa yang izin hari ini ({$hari}, {$tglIndo}): **{$cnt} siswa**.";
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?siswa\s+(yang\s+)?belum\s+absen|(jumlah|total)\s+siswa\s+(yang\s+)?belum\s+absen|siswa\s+(yang\s+)?belum\s+absen\s+(ada\s+)?berapa)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as jml FROM siswa WHERE id NOT IN (SELECT siswa_id FROM absensi_siswa WHERE tanggal = :t)");
            $this->db->bind('t', $tanggal);
            $cnt = (int)$this->db->single()['jml'];
            return "⏳ Total siswa yang belum absen hari ini ({$hari}, {$tglIndo}): **{$cnt} siswa**.";
        }

        if (preg_match('/\b(berapa\s+(jumlah\s+)?guru\s+(yang\s+)?(hadir|masuk)|(jumlah|total)\s+guru\s+(yang\s+)?(hadir|masuk)|guru\s+(yang\s+)?(hadir|masuk)\s+(ada\s+)?berapa)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as jml FROM absensi_guru WHERE tanggal = :t AND (status = 'hadir' OR status = 'terlambat')");
            $this->db->bind('t', $tanggal);
            $cnt = (int)$this->db->single()['jml'];
            return "👨‍🏫 Total guru yang hadir hari ini ({$hari}, {$tglIndo}): **{$cnt} guru**.";
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?guru\s+(yang\s+)?(alpa|alpha)|(jumlah|total)\s+guru\s+(yang\s+)?(alpa|alpha)|guru\s+(yang\s+)?(alpa|alpha)\s+(ada\s+)?berapa)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as jml FROM absensi_guru WHERE tanggal = :t AND status = 'alpa'");
            $this->db->bind('t', $tanggal);
            $cnt = (int)$this->db->single()['jml'];
            return "❌ Total guru yang alpa hari ini ({$hari}, {$tglIndo}): **{$cnt} guru**.";
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?guru\s+(yang\s+)?sakit|(jumlah|total)\s+guru\s+(yang\s+)?sakit|guru\s+(yang\s+)?sakit\s+(ada\s+)?berapa)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as jml FROM absensi_guru WHERE tanggal = :t AND status = 'sakit'");
            $this->db->bind('t', $tanggal);
            $cnt = (int)$this->db->single()['jml'];
            return "🤒 Total guru yang sakit hari ini ({$hari}, {$tglIndo}): **{$cnt} guru**.";
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?guru\s+(yang\s+)?izin|(jumlah|total)\s+guru\s+(yang\s+)?izin|guru\s+(yang\s+)?izin\s+(ada\s+)?berapa)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as jml FROM absensi_guru WHERE tanggal = :t AND status = 'izin'");
            $this->db->bind('t', $tanggal);
            $cnt = (int)$this->db->single()['jml'];
            return "✉️ Total guru yang izin hari ini ({$hari}, {$tglIndo}): **{$cnt} guru**.";
        }
        if (preg_match('/\b(berapa\s+(jumlah\s+)?guru\s+(yang\s+)?terlambat|(jumlah|total)\s+guru\s+(yang\s+)?terlambat|guru\s+(yang\s+)?terlambat\s+(ada\s+)?berapa)\b/i', $clean)) {
            $this->db->query("SELECT COUNT(*) as jml FROM absensi_guru WHERE tanggal = :t AND status = 'terlambat'");
            $this->db->bind('t', $tanggal);
            $cnt = (int)$this->db->single()['jml'];
            return "⚠️ Total guru yang terlambat hari ini ({$hari}, {$tglIndo}): **{$cnt} guru**.";
        }

        // Permintaan Daftar Guru / Kelas Eksplisit
        if (preg_match('/\b(daftar\s+guru|siapa\s+(saja\s+)?guru|sebutkan\s+(nama\s+)?guru|nama\s+nama\s+guru|seluruh\s+guru|semua\s+guru)\b/i', $clean) && !preg_match('/\b(berapa|jumlah|total|hadir|alpa|sakit|izin)\b/i', $clean)) {
            $this->db->query("
                SELECT g.nama, g.nip,
                       (SELECT nama_kelas FROM kelas k WHERE k.wali_kelas_id = g.id LIMIT 1) as wali_kelas
                FROM guru g
                ORDER BY g.id ASC
            ");
            $gurus = $this->db->resultSet();
            $lines = ["👨‍🏫 **Daftar Guru di Database Sekolah:**\n"];
            $i = 1;
            foreach ($gurus as $g) {
                $wk = $g['wali_kelas'] ? " (Wali Kelas {$g['wali_kelas']})" : "";
                $lines[] = "{$i}. {$g['nama']} | NIP: {$g['nip']}{$wk}";
                $i++;
            }
            return implode("\n", $lines);
        }

        if (preg_match('/\b(daftar\s+kelas|apa\s+(saja\s+)?kelas|sebutkan\s+kelas|seluruh\s+kelas|semua\s+kelas)\b/i', $clean) && !preg_match('/\b(berapa|jumlah|total)\b/i', $clean)) {
            $this->db->query("
                SELECT k.nama_kelas, COALESCE(g.nama, 'Belum ditentukan') as nama_wali,
                       (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa
                FROM kelas k
                LEFT JOIN guru g ON k.wali_kelas_id = g.id
                ORDER BY k.id ASC
            ");
            $kelasList = $this->db->resultSet();
            $lines = ["🏫 **Daftar Rombongan Belajar (Kelas):**\n"];
            $i = 1;
            foreach ($kelasList as $kl) {
                $lines[] = "{$i}. Kelas {$kl['nama_kelas']} — Wali Kelas: {$kl['nama_wali']} ({$kl['total_siswa']} siswa)";
                $i++;
            }
            return implode("\n", $lines);
        }

        // 1. Pertanyaan Jumlah Spesifik Kehadiran Pribadi (Berapa kali hadir / alpa / sakit / izin / terlambat)
        if (preg_match('/\b(berapa\s+kali\s+(saya|aku)\s+(hadir|masuk|sakit|izin|alpa|alpha|terlambat))\b/i', $clean) ||
            preg_match('/\b(total\s+(hadir|masuk|sakit|izin|alpa|alpha|terlambat)\s*(saya|aku)?)\b/i', $clean)) {
            if ($role === 'siswa') {
                $sc = $this->getSiswaContext($userId, $tanggal, $hari);
                if ($sc && $sc['profil'] && $sc['rekap']) {
                    $p = $sc['profil'];
                    $rk = $sc['rekap'];
                    $tot = (int)$rk['total_catatan'];

                    if (preg_match('/\b(alpa|alpha)\b/i', $clean)) {
                        return "📊 **Catatan Alpha Siswa (Database):**\n\n" .
                               "- **Nama Siswa:** {$p['nama']} (NIS: {$p['nis']})\n" .
                               "- **Total Alpha:** **{$rk['alpa']}** hari dari **{$tot}** hari tercatat";
                    } elseif (preg_match('/\b(hadir|masuk)\b/i', $clean)) {
                        $pct = $tot > 0 ? round(((int)$rk['hadir'] / $tot) * 100, 1) . '%' : '0%';
                        return "📊 **Catatan Kehadiran Siswa (Database):**\n\n" .
                               "- **Nama Siswa:** {$p['nama']} (NIS: {$p['nis']})\n" .
                               "- **Total Hadir:** **{$rk['hadir']}** hari dari **{$tot}** hari tercatat (Persentase: **{$pct}**)";
                    } elseif (preg_match('/\bsakit\b/i', $clean)) {
                        return "📊 **Catatan Sakit Siswa (Database):**\n\n" .
                               "- **Nama Siswa:** {$p['nama']} (NIS: {$p['nis']})\n" .
                               "- **Total Sakit:** **{$rk['sakit']}** hari dari **{$tot}** hari tercatat";
                    } elseif (preg_match('/\bizin\b/i', $clean)) {
                        return "📊 **Catatan Izin Siswa (Database):**\n\n" .
                               "- **Nama Siswa:** {$p['nama']} (NIS: {$p['nis']})\n" .
                               "- **Total Izin:** **{$rk['izin']}** hari dari **{$tot}** hari tercatat";
                    }
                }
            } elseif ($role === 'guru') {
                $gc = $this->getGuruContext($userId, $tanggal, $hari);
                if ($gc && $gc['profil'] && $gc['rekap']) {
                    $g = $gc['profil'];
                    $rk = $gc['rekap'];
                    $tot = (int)$rk['total_catatan'];

                    if (preg_match('/\b(alpa|alpha)\b/i', $clean)) {
                        return "📊 **Catatan Alpha Guru (Database):**\n\n" .
                               "- **Nama Guru:** {$g['nama']} (NIP: {$g['nip']})\n" .
                               "- **Total Alpha:** **{$rk['alpa']}** hari dari **{$tot}** hari tercatat";
                    } elseif (preg_match('/\bterlambat\b/i', $clean)) {
                        return "📊 **Catatan Terlambat Guru (Database):**\n\n" .
                               "- **Nama Guru:** {$g['nama']} (NIP: {$g['nip']})\n" .
                               "- **Total Terlambat:** **{$rk['terlambat']}** hari dari **{$tot}** hari tercatat";
                    } elseif (preg_match('/\b(hadir|masuk)\b/i', $clean)) {
                        return "📊 **Catatan Hadir Guru (Database):**\n\n" .
                               "- **Nama Guru:** {$g['nama']} (NIP: {$g['nip']})\n" .
                               "- **Hadir (Tepat Waktu):** **{$rk['hadir']}** hari\n" .
                               "- **Hadir (Terlambat):** **{$rk['terlambat']}** hari dari **{$tot}** hari tercatat";
                    } elseif (preg_match('/\bsakit\b/i', $clean)) {
                        return "📊 **Catatan Sakit Guru (Database):**\n\n" .
                               "- **Nama Guru:** {$g['nama']} (NIP: {$g['nip']})\n" .
                               "- **Total Sakit:** **{$rk['sakit']}** hari dari **{$tot}** hari tercatat";
                    } elseif (preg_match('/\bizin\b/i', $clean)) {
                        return "📊 **Catatan Izin Guru (Database):**\n\n" .
                               "- **Nama Guru:** {$g['nama']} (NIP: {$g['nip']})\n" .
                               "- **Total Izin:** **{$rk['izin']}** hari dari **{$tot}** hari tercatat";
                    }
                }
            }
        }

        // 2. Rekap Kelas Binaan (Untuk Guru / Wali Kelas)
        if (preg_match('/\b(rekap\s*(absensi\s*)?kelas|absen\s+kelas|kehadiran\s+kelas)\b/i', $clean) ||
            ($role === 'guru' && preg_match('/\b(kelas\s*(saya|binaan))\b/i', $clean))) {
            $wk = $this->getWaliKelasContext($userId, $tanggal);
            if ($wk && !empty($wk['kelas_wali'])) {
                $rk = $wk['rekap_kelas'];
                $namaK = $wk['kelas_wali']['nama_kelas'];
                $lines = ["🏫 **Rekapitulasi Absensi Kelas Binaan ({$namaK}) Hari Ini:**\n"];
                $lines[] = "📅 **Tanggal:** {$hari}, {$tglIndo}\n";
                $lines[] = "- **Total Siswa:** {$wk['total_siswa']} siswa";
                $lines[] = "- **Hadir:** {$rk['hadir']} siswa";
                $lines[] = "- **Sakit:** {$rk['sakit']} siswa";
                $lines[] = "- **Izin:** {$rk['izin']} siswa";
                $lines[] = "- **Alpha:** {$rk['alpa']} siswa";
                $lines[] = "- **Belum Absen:** {$rk['belum_absen']} siswa\n";

                if (!empty($wk['daftar_absen'])) {
                    $lines[] = "📋 **Daftar Siswa Tidak Hadir / Belum Absen:**";
                    foreach ($wk['daftar_absen'] as $da) {
                        $lines[] = "- {$da}";
                    }
                } else {
                    $lines[] = "- Seluruh siswa di kelas {$namaK} berstatus Hadir hari ini.";
                }
                return implode("\n", $lines);
            }
        }

        // 3. Status Kehadiran Hari Ini (Pribadi)
        $isStatusToday = !preg_match('/\b(rekap|berapa\s+kali)\b/i', $clean) && (
            preg_match('/\b(status\s*(absen|absensi|kehadiran)|cek\s*(absen|absensi|kehadiran)|data\s*(absen|absensi|kehadiran))\b/i', $clean) ||
            preg_match('/\b(sudah\s*(absen|scan|masuk)|belum\s*(absen|scan|masuk)|absen\s*belum|scan\s*belum)\b/i', $clean) ||
            preg_match('/\b(apakah\s+(saya|aku)\s+(sudah|telah|lagi|sedang)?\s*(absen|hadir|masuk|scan|alpa|alpha|sakit|izin))\b/i', $clean) ||
            preg_match('/\b((saya|aku)\s+(sudah|telah)?\s*(absen|scan|masuk)\s*(belum|kah)?)\b/i', $clean) ||
            preg_match('/\b(jam\s+berapa\s+(saya|aku)\s+(absen|scan|masuk|pulang))\b/i', $clean) ||
            preg_match('/\b(absen\s+(saya|aku)\s+(hari\s+ini|apa))\b/i', $clean) ||
            preg_match('/\b(kehadiran\s+(saya|aku)\s*(hari\s+ini)?)\b/i', $clean) ||
            preg_match('/\b((saya|aku)\s+(hari\s+ini\s+)?(hadir|masuk|alpa|alpha|sakit|izin))\b/i', $clean) ||
            preg_match('/\b(hadir\s+atau\s+(alpa|alpha|tidak))\b/i', $clean) ||
            $clean === 'absen saya' || $clean === 'absensi saya' || $clean === 'kehadiran saya' || $clean === 'status saya'
        );

        if ($isStatusToday) {
            if ($role === 'siswa') {
                $sc = $this->getSiswaContext($userId, $tanggal, $hari);
                if ($sc && $sc['profil']) {
                    $p = $sc['profil'];
                    $ab = $sc['absen_hari_ini'];
                    if ($ab) {
                        $st = $this->formatStatusSiswa($ab['status']);
                        $jam = substr($ab['jam'], 0, 5);
                        return "📋 **Status Absensi Siswa Hari Ini**\n" .
                               "📅 **Tanggal:** {$hari}, {$tglIndo}\n\n" .
                               "- **Nama Siswa:** {$p['nama']} (NIS: {$p['nis']})\n" .
                               "- **Kelas:** " . ($p['nama_kelas'] ?? '-') . "\n" .
                               "- **Status Kehadiran:** **{$st}**\n" .
                               "- **Waktu:** Pukul **{$jam} WIB**";
                    } else {
                        return "📋 **Status Absensi Siswa Hari Ini**\n" .
                               "📅 **Tanggal:** {$hari}, {$tglIndo}\n\n" .
                               "- **Nama Siswa:** {$p['nama']} (NIS: {$p['nis']})\n" .
                               "- **Kelas:** " . ($p['nama_kelas'] ?? '-') . "\n" .
                               "- **Status Kehadiran:** **Belum Absen**";
                    }
                }
            } elseif ($role === 'guru') {
                $gc = $this->getGuruContext($userId, $tanggal, $hari);
                if ($gc && $gc['profil']) {
                    $g = $gc['profil'];
                    $ag = $gc['absen_hari_ini'];
                    if ($ag) {
                        $st = $this->formatStatusGuru($ag['status']);
                        $jamMasuk = substr($ag['jam_masuk'], 0, 5);
                        $jamPulang = !empty($ag['jam_pulang']) ? substr($ag['jam_pulang'], 0, 5) . " WIB" : "Belum scan pulang";
                        return "📋 **Status Absensi Guru Hari Ini**\n" .
                               "📅 **Tanggal:** {$hari}, {$tglIndo}\n\n" .
                               "- **Nama Guru:** {$g['nama']} (NIP: {$g['nip']})\n" .
                               "- **Status Kehadiran:** **{$st}**\n" .
                               "- **Jam Masuk:** Pukul **{$jamMasuk} WIB**\n" .
                               "- **Jam Pulang:** **{$jamPulang}**";
                    } else {
                        return "📋 **Status Absensi Guru Hari Ini**\n" .
                               "📅 **Tanggal:** {$hari}, {$tglIndo}\n\n" .
                               "- **Nama Guru:** {$g['nama']} (NIP: {$g['nip']})\n" .
                               "- **Status Kehadiran:** **Belum Absen Masuk**";
                    }
                }
            }
        }

        // 4. Rekapitulasi Kehadiran Pribadi Lengkap
        if (
            preg_match('/\b(rekap(itulasi)?\s*(absensi|kehadiran|absen)?\s*(saya|aku)?)\b/i', $clean) ||
            preg_match('/\b(total\s+(kehadiran|absensi|absen)\s*(saya|aku)?)\b/i', $clean) ||
            preg_match('/\b(persentase\s+kehadiran\s*(saya|aku)?)\b/i', $clean)
        ) {
            if ($role === 'siswa') {
                $sc = $this->getSiswaContext($userId, $tanggal, $hari);
                if ($sc && $sc['profil'] && $sc['rekap']) {
                    $p = $sc['profil'];
                    $rk = $sc['rekap'];
                    $tot = (int)$rk['total_catatan'];
                    $hdr = (int)$rk['hadir'];
                    $skt = (int)$rk['sakit'];
                    $izn = (int)$rk['izin'];
                    $alp = (int)$rk['alpa'];
                    $persen = $tot > 0 ? round(($hdr / $tot) * 100, 1) . '%' : '0%';

                    return "📊 **Rekapitulasi Kehadiran Siswa (Database):**\n\n" .
                           "- **Nama Siswa:** {$p['nama']} (NIS: {$p['nis']})\n" .
                           "- **Kelas:** " . ($p['nama_kelas'] ?? '-') . "\n" .
                           "- **Hadir:** **{$hdr}** hari\n" .
                           "- **Sakit:** **{$skt}** hari\n" .
                           "- **Izin:** **{$izn}** hari\n" .
                           "- **Alpha:** **{$alp}** hari\n" .
                           "- **Total Hari Tercatat:** **{$tot}** hari\n" .
                           "- **Persentase Kehadiran:** **{$persen}**";
                }
            } elseif ($role === 'guru') {
                $gc = $this->getGuruContext($userId, $tanggal, $hari);
                if ($gc && $gc['profil'] && $gc['rekap']) {
                    $g = $gc['profil'];
                    $rk = $gc['rekap'];
                    $tot = (int)$rk['total_catatan'];
                    $hdr = (int)$rk['hadir'];
                    $tlb = (int)$rk['terlambat'];
                    $skt = (int)$rk['sakit'];
                    $izn = (int)$rk['izin'];
                    $alp = (int)$rk['alpa'];

                    return "📊 **Rekapitulasi Kehadiran Guru (Database):**\n\n" .
                           "- **Nama Guru:** {$g['nama']} (NIP: {$g['nip']})\n" .
                           "- **Hadir (Tepat Waktu):** **{$hdr}** hari\n" .
                           "- **Hadir (Terlambat):** **{$tlb}** hari\n" .
                           "- **Sakit:** **{$skt}** hari\n" .
                           "- **Izin:** **{$izn}** hari\n" .
                           "- **Alpha:** **{$alp}** hari\n" .
                           "- **Total Hari Tercatat:** **{$tot}** hari";
                }
            }
        }

        // 4. Riwayat Absensi Pribadi
        if (
            preg_match('/\b(riwayat\s*(absensi|kehadiran|absen)?\s*(saya|aku)?)\b/i', $clean) ||
            preg_match('/\b(catatan\s*(absensi|kehadiran|absen)\s*(saya|aku)?)\b/i', $clean) ||
            preg_match('/\b(log\s*(absensi|kehadiran|absen))\b/i', $clean) ||
            preg_match('/\b(daftar\s+hadir\s*(saya|aku))\b/i', $clean)
        ) {
            if ($role === 'siswa') {
                $sc = $this->getSiswaContext($userId, $tanggal, $hari);
                if ($sc && !empty($sc['riwayat'])) {
                    $lines = ["📜 **Riwayat Absensi Siswa (Database):**\n"];
                    $lines[] = "- **Siswa:** {$sc['profil']['nama']} (NIS: {$sc['profil']['nis']})\n";
                    $i = 1;
                    foreach ($sc['riwayat'] as $rw) {
                        $st = $this->formatStatusSiswa($rw['status']);
                        $hIndo = $this->getHariIndo($rw['tanggal']);
                        $tIndo = $this->formatTanggalIndo($rw['tanggal']);
                        $jam = substr($rw['jam'], 0, 5);
                        $lines[] = "{$i}. **{$hIndo}, {$tIndo}** — Pukul {$jam} WIB | Status: **{$st}**";
                        $i++;
                    }
                    return implode("\n", $lines);
                } else {
                    return "📜 Belum ada catatan riwayat absensi untuk akun Anda di database.";
                }
            } elseif ($role === 'guru') {
                $gc = $this->getGuruContext($userId, $tanggal, $hari);
                if ($gc && !empty($gc['riwayat'])) {
                    $lines = ["📜 **Riwayat Absensi Guru (Database):**\n"];
                    $lines[] = "- **Guru:** {$gc['profil']['nama']} (NIP: {$gc['profil']['nip']})\n";
                    $i = 1;
                    foreach ($gc['riwayat'] as $rw) {
                        $st = $this->formatStatusGuru($rw['status']);
                        $hIndo = $this->getHariIndo($rw['tanggal']);
                        $tIndo = $this->formatTanggalIndo($rw['tanggal']);
                        $jamM = substr($rw['jam_masuk'], 0, 5);
                        $jamP = !empty($rw['jam_pulang']) ? substr($rw['jam_pulang'], 0, 5) . " WIB" : "-";
                        $lines[] = "{$i}. **{$hIndo}, {$tIndo}** | Masuk: {$jamM} WIB, Pulang: {$jamP} | Status: **{$st}**";
                        $i++;
                    }
                    return implode("\n", $lines);
                } else {
                    return "📜 Belum ada catatan riwayat absensi guru untuk akun Anda di database.";
                }
            }
        }

        // 5. Rekapitulasi Kehadiran Sekolah Hari Ini
        if (
            preg_match('/\b(rekap(itulasi)?\s*(absensi|kehadiran)\s*(hari\s+ini|sekolah))\b/i', $clean) ||
            preg_match('/\b(berapa\s+(siswa|guru)\s+(yang\s+)?(hadir|masuk|alpa|alpha|sakit|izin|belum\s+absen)\s*(hari\s+ini)?)\b/i', $clean) ||
            preg_match('/\b(kehadiran\s+(siswa|guru)\s+hari\s+ini)\b/i', $clean)
        ) {
            $rkS = $this->getRekapSiswaHariIni($tanggal);
            $this->db->query("SELECT COUNT(*) as total FROM siswa");
            $totSiswa = (int)$this->db->single()['total'];

            $this->db->query("SELECT COUNT(*) as total FROM guru");
            $totGuru = (int)$this->db->single()['total'];

            $this->db->query("SELECT status, COUNT(*) as jml FROM absensi_guru WHERE tanggal = :tanggal GROUP BY status");
            $this->db->bind('tanggal', $tanggal);
            $rkGRows = $this->db->resultSet();
            $rkG = ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
            foreach ($rkGRows as $rg) {
                $st = strtolower(trim($rg['status']));
                if (isset($rkG[$st])) $rkG[$st] += (int)$rg['jml'];
            }

            return "📊 **Rekapitulasi Kehadiran Sekolah Hari Ini ({$hari}, {$tglIndo}):**\n\n" .
                   "**Siswa (Total: {$totSiswa} siswa):**\n" .
                   "- Hadir: {$rkS['hadir']} siswa\n" .
                   "- Sakit: {$rkS['sakit']} siswa\n" .
                   "- Izin: {$rkS['izin']} siswa\n" .
                   "- Alpha: {$rkS['alpa']} siswa\n\n" .
                   "**Guru (Total: {$totGuru} guru):**\n" .
                   "- Hadir (Tepat Waktu): {$rkG['hadir']} guru\n" .
                   "- Hadir (Terlambat): {$rkG['terlambat']} guru\n" .
                   "- Sakit: {$rkG['sakit']} guru\n" .
                   "- Izin: {$rkG['izin']} guru\n" .
                   "- Alpha: {$rkG['alpa']} guru";
        }

        // 6. Daftar Siswa / Guru Berdasarkan Status Absensi Hari Ini
        if (preg_match('/\b(siapa|daftar)\s+(saja\s+)?(siswa|guru)?\s*(yang\s+)?(alpa|alpha|sakit|izin|hadir|terlambat|belum\s+absen)\b/i', $clean)) {
            $targetStatus = 'hadir';
            if (preg_match('/\b(alpa|alpha)\b/i', $clean)) $targetStatus = 'alpa';
            elseif (preg_match('/\b(sakit)\b/i', $clean)) $targetStatus = 'sakit';
            elseif (preg_match('/\b(izin)\b/i', $clean)) $targetStatus = 'izin';
            elseif (preg_match('/\b(terlambat)\b/i', $clean)) $targetStatus = 'terlambat';
            elseif (preg_match('/\b(belum\s+absen)\b/i', $clean)) $targetStatus = 'belum_absen';

            $isGuruSearch = (bool)preg_match('/\bguru\b/i', $clean);

            if ($isGuruSearch) {
                $this->db->query("
                    SELECT g.nama, g.nip, ag.jam_masuk, ag.jam_pulang, ag.status
                    FROM absensi_guru ag
                    JOIN guru g ON ag.guru_id = g.id
                    WHERE ag.tanggal = :tanggal AND ag.status = :st
                    ORDER BY g.nama ASC
                ");
                $this->db->bind('tanggal', $tanggal);
                $this->db->bind('st', $targetStatus);
                $rows = $this->db->resultSet();

                $label = ucfirst($targetStatus);
                $lines = ["📋 **Daftar Guru Status {$label} Hari Ini ({$hari}, {$tglIndo}):**\n"];
                if (empty($rows)) {
                    $lines[] = "- Tidak ada guru dengan status {$label} hari ini.";
                } else {
                    $i = 1;
                    foreach ($rows as $r) {
                        $jm = substr($r['jam_masuk'], 0, 5);
                        $lines[] = "{$i}. {$r['nama']} (NIP: {$r['nip']}) — Pukul {$jm} WIB";
                        $i++;
                    }
                }
                return implode("\n", $lines);
            } else {
                $statusMapSiswa = [
                    'hadir'     => 0,
                    'sakit'     => 1,
                    'izin'      => 2,
                    'alpa'      => 3,
                ];
                $label = ucfirst($targetStatus);
                $lines = ["📋 **Daftar Siswa Status {$label} Hari Ini ({$hari}, {$tglIndo}):**\n"];

                if ($targetStatus === 'belum_absen') {
                    $this->db->query("
                        SELECT s.nama, s.nis, k.nama_kelas
                        FROM siswa s
                        LEFT JOIN kelas k ON s.kelas_id = k.id
                        WHERE s.id NOT IN (SELECT siswa_id FROM absensi_siswa WHERE tanggal = :tanggal)
                        ORDER BY k.nama_kelas ASC, s.nama ASC
                    ");
                    $this->db->bind('tanggal', $tanggal);
                    $rows = $this->db->resultSet();
                } else {
                    $stCode = $statusMapSiswa[$targetStatus] ?? 0;
                    $this->db->query("
                        SELECT s.nama, s.nis, k.nama_kelas, a.jam
                        FROM absensi_siswa a
                        JOIN siswa s ON a.siswa_id = s.id
                        LEFT JOIN kelas k ON s.kelas_id = k.id
                        WHERE a.tanggal = :tanggal AND a.status = :st
                        ORDER BY k.nama_kelas ASC, s.nama ASC
                    ");
                    $this->db->bind('tanggal', $tanggal);
                    $this->db->bind('st', $stCode);
                    $rows = $this->db->resultSet();
                }

                if (empty($rows)) {
                    $lines[] = "- Tidak ada siswa dengan status {$label} hari ini.";
                } else {
                    $i = 1;
                    foreach ($rows as $r) {
                        $kelas = $r['nama_kelas'] ?? '-';
                        $jam = isset($r['jam']) ? ' (Pukul ' . substr($r['jam'], 0, 5) . ' WIB)' : '';
                        $lines[] = "{$i}. {$r['nama']} (NIS: {$r['nis']}, Kelas: {$kelas}){$jam}";
                        $i++;
                    }
                }
                return implode("\n", $lines);
            }
        }

        // 7. Jadwal Pelajaran (Siswa) atau Jadwal Mengajar (Guru)
        if (
            preg_match('/\b(jadwal\s*(pelajaran|mengajar|sekolah|kelas|hari ini|besok|seminggu)?)\b/i', $clean) ||
            preg_match('/\b(belajar\s+apa\s*(hari\s+ini)?)\b/i', $clean) ||
            preg_match('/\b(mengajar\s+apa\s*(hari\s+ini)?)\b/i', $clean)
        ) {
            $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            $targetHari = $hari;
            foreach ($hariList as $hl) {
                if (str_contains($clean, strtolower($hl))) {
                    $targetHari = $hl;
                    break;
                }
            }

            if ($role === 'siswa') {
                $sc = $this->getSiswaContext($userId, $tanggal, $targetHari);
                if ($sc && $sc['profil']) {
                    $namaKelas = $sc['profil']['nama_kelas'] ?? '-';
                    $jadwalTarget = ($targetHari === $hari) ? $sc['jadwal_hari_ini'] : [];
                    if ($targetHari !== $hari && !empty($sc['profil']['kelas_id'])) {
                        $this->db->query("
                            SELECT j.hari, j.mata_pelajaran, j.jam_mulai, j.jam_selesai, g.nama as nama_guru
                            FROM jadwal j
                            JOIN guru g ON j.guru_id = g.id
                            WHERE j.kelas_id = :kelas_id AND j.hari = :hari
                            ORDER BY j.jam_mulai ASC
                        ");
                        $this->db->bind('kelas_id', $sc['profil']['kelas_id']);
                        $this->db->bind('hari', $targetHari);
                        $jadwalTarget = $this->db->resultSet();
                    }

                    if (!empty($jadwalTarget)) {
                        $lines = ["📅 **Jadwal Pelajaran Kelas {$namaKelas} — Hari {$targetHari}:**\n"];
                        $i = 1;
                        foreach ($jadwalTarget as $jd) {
                            $jm = substr($jd['jam_mulai'], 0, 5);
                            $js = substr($jd['jam_selesai'], 0, 5);
                            $mapel = !empty($jd['mata_pelajaran']) ? $jd['mata_pelajaran'] : 'Mata Pelajaran Umum';
                            $lines[] = "{$i}. Pukul {$jm} - {$js} WIB | Mapel: {$mapel} | Guru: {$jd['nama_guru']}";
                            $i++;
                        }
                        return implode("\n", $lines);
                    } else {
                        return "📅 Tidak ada jadwal pelajaran yang tercatat di database untuk kelas **{$namaKelas}** pada hari **{$targetHari}**.";
                    }
                }
            } elseif ($role === 'guru') {
                $gc = $this->getGuruContext($userId, $tanggal, $targetHari);
                if ($gc && $gc['profil']) {
                    $jadwalTarget = ($targetHari === $hari) ? $gc['jadwal_hari_ini'] : [];
                    if ($targetHari !== $hari) {
                        $this->db->query("
                            SELECT j.hari, j.mata_pelajaran, j.jam_mulai, j.jam_selesai, k.nama_kelas
                            FROM jadwal j
                            JOIN kelas k ON j.kelas_id = k.id
                            WHERE j.guru_id = :guru_id AND j.hari = :hari
                            ORDER BY j.jam_mulai ASC
                        ");
                        $this->db->bind('guru_id', $gc['profil']['id']);
                        $this->db->bind('hari', $targetHari);
                        $jadwalTarget = $this->db->resultSet();
                    }

                    if (!empty($jadwalTarget)) {
                        $lines = ["📅 **Jadwal Mengajar Anda — Hari {$targetHari}:**\n"];
                        $i = 1;
                        foreach ($jadwalTarget as $jd) {
                            $jm = substr($jd['jam_mulai'], 0, 5);
                            $js = substr($jd['jam_selesai'], 0, 5);
                            $mapel = !empty($jd['mata_pelajaran']) ? $jd['mata_pelajaran'] : 'Mata Pelajaran';
                            $lines[] = "{$i}. Pukul {$jm} - {$js} WIB | Mapel: {$mapel} | Kelas: {$jd['nama_kelas']}";
                            $i++;
                        }
                        return implode("\n", $lines);
                    } else {
                        return "📅 Anda tidak memiliki jadwal mengajar di database pada hari **{$targetHari}**.";
                    }
                }
            }
        }

        // 8. Wali Kelas (Khusus Siswa)
        if ($role === 'siswa' && preg_match('/\b(wali\s+kelas\s*(saya|aku)?)\b/i', $clean)) {
            $sc = $this->getSiswaContext($userId, $tanggal, $hari);
            if ($sc && $sc['profil']) {
                $p = $sc['profil'];
                return "🏫 **Wali Kelas:**\n\n" .
                       "- **Nama Wali Kelas:** {$p['nama_wali']}\n" .
                       "- **Kelas:** " . ($p['nama_kelas'] ?? '-');
            }
        }

        // 9. Rekap Kelas Binaan (Untuk Guru yang merangkap Wali Kelas)
        if ($role === 'guru' && preg_match('/\b(rekap\s*(absensi\s*)?kelas\s*(saya|binaan)?|absen\s+kelas\s*(saya|binaan)?|siapa\s+siswa\s+(alpa|sakit|izin|tidak\s+hadir))\b/i', $clean)) {
            $wk = $this->getWaliKelasContext($userId, $tanggal);
            if ($wk && !empty($wk['kelas_wali'])) {
                $rk = $wk['rekap_kelas'];
                $namaK = $wk['kelas_wali']['nama_kelas'];
                $lines = ["🏫 **Rekapitulasi Absensi Kelas Binaan ({$namaK}) Hari Ini:**\n"];
                $lines[] = "📅 **Tanggal:** {$hari}, {$tglIndo}\n";
                $lines[] = "- **Total Siswa:** {$wk['total_siswa']} siswa";
                $lines[] = "- **Hadir:** {$rk['hadir']} siswa";
                $lines[] = "- **Sakit:** {$rk['sakit']} siswa";
                $lines[] = "- **Izin:** {$rk['izin']} siswa";
                $lines[] = "- **Alpha:** {$rk['alpa']} siswa";
                $lines[] = "- **Belum Absen:** {$rk['belum_absen']} siswa\n";

                if (!empty($wk['daftar_absen'])) {
                    $lines[] = "📋 **Daftar Siswa Tidak Hadir / Belum Absen:**";
                    foreach ($wk['daftar_absen'] as $da) {
                        $lines[] = "- {$da}";
                    }
                } else {
                    $lines[] = "- Seluruh siswa di kelas {$namaK} berstatus Hadir hari ini.";
                }
                return implode("\n", $lines);
            }
        }

        // 10. Barcode ID Pengguna
        if (preg_match('/\b(barcode\s*(id)?\s*(saya|aku|diriku)?|kode\s*barcode|kartu\s*absen)\b/i', $clean)) {
            if ($role === 'siswa') {
                $sc = $this->getSiswaContext($userId, $tanggal, $hari);
                if ($sc && $sc['profil']) {
                    $bc = !empty($sc['profil']['barcode_id']) ? $sc['profil']['barcode_id'] : 'Belum di-generate';
                    return "🪪 **Barcode ID:**\n\n" .
                           "- **Nama:** {$sc['profil']['nama']}\n" .
                           "- **NIS:** {$sc['profil']['nis']}\n" .
                           "- **Barcode ID:** `{$bc}`";
                }
            } elseif ($role === 'guru') {
                $gc = $this->getGuruContext($userId, $tanggal, $hari);
                if ($gc && $gc['profil']) {
                    $bc = !empty($gc['profil']['barcode_id']) ? $gc['profil']['barcode_id'] : 'Belum di-generate';
                    return "🪪 **Barcode ID:**\n\n" .
                           "- **Nama:** {$gc['profil']['nama']}\n" .
                           "- **NIP:** {$gc['profil']['nip']}\n" .
                           "- **Barcode ID:** `{$bc}`";
                }
            }
        }

        return null;
    }

    /**
     * Ringkasan statistik sistem absensi
     */
    public function getSystemStats(): array {
        $stats = [];

        $this->db->query("SELECT COUNT(*) as total FROM siswa");
        $stats['total_siswa'] = (int)($this->db->single()['total'] ?? 0);

        $this->db->query("SELECT COUNT(*) as total FROM guru");
        $stats['total_guru'] = (int)($this->db->single()['total'] ?? 0);

        $this->db->query("SELECT COUNT(*) as total FROM kelas");
        $stats['total_kelas'] = (int)($this->db->single()['total'] ?? 0);

        $this->db->query("SELECT COUNT(*) as total FROM users");
        $stats['total_user'] = (int)($this->db->single()['total'] ?? 0);

        // Daftar kelas beserta wali kelas dan jumlah siswa
        $this->db->query("
            SELECT k.id, k.nama_kelas, COALESCE(g.nama, 'Belum ditentukan') as nama_wali,
                   (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as jumlah_siswa
            FROM kelas k
            LEFT JOIN guru g ON k.wali_kelas_id = g.id
            ORDER BY k.nama_kelas ASC
        ");
        $stats['daftar_kelas'] = $this->db->resultSet();

        return $stats;
    }

    /**
     * Rekap absensi siswa hari ini
     */
    public function getRekapSiswaHariIni(string $tanggal): array {
        $this->db->query("
            SELECT status, COUNT(*) as total
            FROM absensi_siswa
            WHERE tanggal = :tanggal
            GROUP BY status
        ");
        $this->db->bind('tanggal', $tanggal);
        $rows = $this->db->resultSet();

        $rekap = [
            'hadir'     => 0,
            'terlambat' => 0,
            'sakit'     => 0,
            'izin'      => 0,
            'alpa'      => 0,
            'total'     => 0
        ];
        foreach ($rows as $r) {
            $raw = $r['status'];
            $st = (int)$raw;
            switch ($st) {
                case 0: $rekap['hadir'] += (int)$r['total']; break;
                case 1: $rekap['sakit'] += (int)$r['total']; break;
                case 2: $rekap['izin']  += (int)$r['total']; break;
                case 3: $rekap['alpa']  += (int)$r['total']; break;
            }
            $rekap['total'] += (int)$r['total'];
        }
        return $rekap;
    }

    /**
     * Rekap absensi guru hari ini
     */
    public function getRekapGuruHariIni(string $tanggal): array {
        $this->db->query("SELECT COUNT(*) as total FROM guru");
        $totalGuru = (int)($this->db->single()['total'] ?? 0);

        $this->db->query("
            SELECT status, COUNT(*) as jml 
            FROM absensi_guru 
            WHERE tanggal = :tanggal
            GROUP BY status
        ");
        $this->db->bind('tanggal', $tanggal);
        $rows = $this->db->resultSet();

        $rekap = [
            'total_guru' => $totalGuru,
            'hadir'      => 0,
            'terlambat'  => 0,
            'sakit'      => 0,
            'izin'       => 0,
            'alpa'       => 0,
            'tercatat'   => 0
        ];

        foreach ($rows as $r) {
            $st = strtolower(trim($r['status']));
            if (isset($rekap[$st])) {
                $rekap[$st] += (int)$r['jml'];
            }
            $rekap['tercatat'] += (int)$r['jml'];
        }
        $rekap['tidak_hadir'] = max(0, $totalGuru - ($rekap['hadir'] + $rekap['terlambat']));
        return $rekap;
    }

    /**
     * Ambil data lengkap siswa (Profil, status hari ini, rekap, riwayat, jadwal)
     */
    public function getSiswaContext(int $userId, string $tanggal, string $hari): ?array {
        $this->db->query("
            SELECT s.*, k.nama_kelas, COALESCE(g.nama, 'Belum ditentukan') as nama_wali
            FROM siswa s
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN guru g ON k.wali_kelas_id = g.id
            WHERE s.user_id = :user_id
        ");
        $this->db->bind('user_id', $userId);
        $siswa = $this->db->single();

        if (!$siswa) return null;

        // Status absensi hari ini
        $this->db->query("
            SELECT jam, status 
            FROM absensi_siswa 
            WHERE siswa_id = :siswa_id AND tanggal = :tanggal
            ORDER BY id DESC LIMIT 1
        ");
        $this->db->bind('siswa_id', $siswa['id']);
        $this->db->bind('tanggal', $tanggal);
        $absenHariIni = $this->db->single();

        // Rekap total absensi (0=hadir, 1=sakit, 2=izin, 3=alpa)
        $this->db->query("
            SELECT 
                COUNT(*) as total_catatan,
                SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as hadir,
                0 as terlambat,
                SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as alpa
            FROM absensi_siswa 
            WHERE siswa_id = :siswa_id
        ");
        $this->db->bind('siswa_id', $siswa['id']);
        $rekap = $this->db->single();

        // 5 riwayat absensi terbaru
        $this->db->query("
            SELECT tanggal, jam, status 
            FROM absensi_siswa 
            WHERE siswa_id = :siswa_id 
            ORDER BY tanggal DESC, jam DESC 
            LIMIT 5
        ");
        $this->db->bind('siswa_id', $siswa['id']);
        $riwayat = $this->db->resultSet();

        // Jadwal pelajaran kelas hari ini
        $jadwalHariIni = [];
        $jadwalSeminggu = [];
        if (!empty($siswa['kelas_id'])) {
            $this->db->query("
                SELECT j.hari, j.mata_pelajaran, j.jam_mulai, j.jam_selesai, g.nama as nama_guru
                FROM jadwal j
                JOIN guru g ON j.guru_id = g.id
                WHERE j.kelas_id = :kelas_id AND j.hari = :hari
                ORDER BY j.jam_mulai ASC
            ");
            $this->db->bind('kelas_id', $siswa['kelas_id']);
            $this->db->bind('hari', $hari);
            $jadwalHariIni = $this->db->resultSet();

            $this->db->query("
                SELECT j.hari, j.mata_pelajaran, j.jam_mulai, j.jam_selesai, g.nama as nama_guru
                FROM jadwal j
                JOIN guru g ON j.guru_id = g.id
                WHERE j.kelas_id = :kelas_id
                ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai ASC
            ");
            $this->db->bind('kelas_id', $siswa['kelas_id']);
            $jadwalSeminggu = $this->db->resultSet();
        }

        return [
            'profil'          => $siswa,
            'absen_hari_ini'  => $absenHariIni ?: null,
            'rekap'           => $rekap,
            'riwayat'         => $riwayat,
            'jadwal_hari_ini' => $jadwalHariIni,
            'jadwal_seminggu' => $jadwalSeminggu
        ];
    }

    /**
     * Ambil data lengkap guru (Profil, absensi hari ini, rekap, riwayat, jadwal mengajar, kelas wali)
     */
    public function getGuruContext(int $userId, string $tanggal, string $hari): ?array {
        $this->db->query("
            SELECT g.*, u.username
            FROM guru g
            JOIN users u ON g.user_id = u.id
            WHERE g.user_id = :user_id
        ");
        $this->db->bind('user_id', $userId);
        $guru = $this->db->single();

        if (!$guru) return null;

        // Cek kelas binaan (wali kelas)
        $this->db->query("SELECT id, nama_kelas FROM kelas WHERE wali_kelas_id = :guru_id");
        $this->db->bind('guru_id', $guru['id']);
        $kelasWali = $this->db->single();

        // Absensi hari ini
        $this->db->query("
            SELECT jam_masuk, jam_pulang, status 
            FROM absensi_guru 
            WHERE guru_id = :guru_id AND tanggal = :tanggal
        ");
        $this->db->bind('guru_id', $guru['id']);
        $this->db->bind('tanggal', $tanggal);
        $absenHariIni = $this->db->single();

        // Rekap total absensi guru
        $this->db->query("
            SELECT 
                COUNT(*) as total_catatan,
                SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as terlambat,
                SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status = 'izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = 'alpa' THEN 1 ELSE 0 END) as alpa
            FROM absensi_guru 
            WHERE guru_id = :guru_id
        ");
        $this->db->bind('guru_id', $guru['id']);
        $rekapGuru = $this->db->single();

        // 7 riwayat absensi guru terbaru
        $this->db->query("
            SELECT tanggal, jam_masuk, jam_pulang, status 
            FROM absensi_guru 
            WHERE guru_id = :guru_id 
            ORDER BY tanggal DESC, jam_masuk DESC 
            LIMIT 7
        ");
        $this->db->bind('guru_id', $guru['id']);
        $riwayatGuru = $this->db->resultSet();

        // Jadwal mengajar hari ini
        $this->db->query("
            SELECT j.hari, j.mata_pelajaran, j.jam_mulai, j.jam_selesai, k.nama_kelas
            FROM jadwal j
            JOIN kelas k ON j.kelas_id = k.id
            WHERE j.guru_id = :guru_id AND j.hari = :hari
            ORDER BY j.jam_mulai ASC
        ");
        $this->db->bind('guru_id', $guru['id']);
        $this->db->bind('hari', $hari);
        $jadwalHariIni = $this->db->resultSet();

        // Jadwal mengajar seminggu
        $this->db->query("
            SELECT j.hari, j.mata_pelajaran, j.jam_mulai, j.jam_selesai, k.nama_kelas
            FROM jadwal j
            JOIN kelas k ON j.kelas_id = k.id
            WHERE j.guru_id = :guru_id
            ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai ASC
        ");
        $this->db->bind('guru_id', $guru['id']);
        $jadwalSeminggu = $this->db->resultSet();

        return [
            'profil'          => $guru,
            'kelas_wali'      => $kelasWali ?: null,
            'absen_hari_ini'  => $absenHariIni ?: null,
            'rekap'           => $rekapGuru,
            'riwayat'         => $riwayatGuru,
            'jadwal_hari_ini' => $jadwalHariIni,
            'jadwal_seminggu' => $jadwalSeminggu
        ];
    }

    /**
     * Ambil data lengkap wali kelas (Profil, kelas binaan, siswa & kehadiran hari ini)
     */
    public function getWaliKelasContext(int $userId, string $tanggal): ?array {
        $this->db->query("
            SELECT g.*, k.id as kelas_id, k.nama_kelas
            FROM guru g
            JOIN kelas k ON k.wali_kelas_id = g.id
            WHERE g.user_id = :user_id
        ");
        $this->db->bind('user_id', $userId);
        $data = $this->db->single();

        if (!$data) {
            // Cek jika akun wali kelas belum di-link di tabel guru/kelas
            $this->db->query("SELECT * FROM guru WHERE user_id = :user_id");
            $this->db->bind('user_id', $userId);
            $guru = $this->db->single();
            return [
                'profil'      => $guru ?: ['nama' => 'Wali Kelas', 'nip' => '-'],
                'kelas_wali'  => null,
                'total_siswa' => 0,
                'rekap_kelas' => ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0, 'belum_absen' => 0],
                'siswa_absen' => []
            ];
        }

        $kelasId = (int)$data['kelas_id'];

        // Daftar seluruh siswa di kelas binaan dengan status absensi hari ini
        $this->db->query("
            SELECT s.id, s.nis, s.nama, a.jam, a.status
            FROM siswa s
            LEFT JOIN absensi_siswa a ON s.id = a.siswa_id AND a.tanggal = :tanggal
            WHERE s.kelas_id = :kelas_id
            ORDER BY s.nama ASC
        ");
        $this->db->bind('tanggal', $tanggal);
        $this->db->bind('kelas_id', $kelasId);
        $siswaList = $this->db->resultSet();

        $rekapKelas = [
            'hadir'       => 0,
            'terlambat'   => 0,
            'sakit'       => 0,
            'izin'        => 0,
            'alpa'        => 0,
            'belum_absen' => 0
        ];

        $tidakHadir = [];
        foreach ($siswaList as $s) {
            if ($s['status'] === null || $s['status'] === '') {
                $rekapKelas['belum_absen']++;
                $tidakHadir[] = $s['nama'] . " (Belum absen)";
            } else {
                $st = (int)$s['status'];
                switch ($st) {
                    case 0:
                        $rekapKelas['hadir']++;
                        break;
                    case 1:
                        $rekapKelas['sakit']++;
                        $tidakHadir[] = $s['nama'] . " (Sakit)";
                        break;
                    case 2:
                        $rekapKelas['izin']++;
                        $tidakHadir[] = $s['nama'] . " (Izin)";
                        break;
                    case 3:
                        $rekapKelas['alpa']++;
                        $tidakHadir[] = $s['nama'] . " (Alpha)";
                        break;
                }
            }
        }

        return [
            'profil'          => $data,
            'kelas_wali'      => ['id' => $data['kelas_id'], 'nama_kelas' => $data['nama_kelas']],
            'total_siswa'     => count($siswaList),
            'rekap_kelas'     => $rekapKelas,
            'siswa_list'      => $siswaList,
            'daftar_absen'    => $tidakHadir
        ];
    }

    /**
     * Pencarian dinamis database berdasarkan pertanyaan pengguna.
     * Mampu menjawab pertanyaan tentang Guru, Siswa, Kelas, Jadwal, Barcode, Kehadiran, dan Akun.
     */
    public function searchDatabase(string $query, string $role, int $userId, string $tanggal): array {
        $results = [];
        $q = strtolower(trim($query));

        // 1. Pencarian Barcode (misal: "BC-GURU-001", "RFID-S-001", "BC-SISWA...")
        if (preg_match('/(BC-[A-Za-z0-9\-]+|RFID-[A-Za-z0-9\-]+)/i', $query, $bm)) {
            $bcSearch = trim($bm[1]);
            // Cari di Guru
            $this->db->query("
                SELECT g.id, g.nama, g.nip, g.barcode_id,
                       (SELECT nama_kelas FROM kelas k WHERE k.wali_kelas_id = g.id LIMIT 1) as wali_kelas
                FROM guru g 
                WHERE LOWER(g.barcode_id) LIKE LOWER(:bc)
            ");
            $this->db->bind('bc', '%' . $bcSearch . '%');
            $bg = $this->db->single();
            if ($bg) {
                $results['barcode_match_guru'] = $bg;
            }

            // Cari di Siswa
            $this->db->query("
                SELECT s.id, s.nama, s.nis, s.barcode_id, k.nama_kelas,
                       (SELECT status FROM absensi_siswa a WHERE a.siswa_id = s.id AND a.tanggal = :tanggal LIMIT 1) as status_hari_ini
                FROM siswa s 
                LEFT JOIN kelas k ON s.kelas_id = k.id 
                WHERE LOWER(s.barcode_id) LIKE LOWER(:bc)
            ");
            $this->db->bind('bc', '%' . $bcSearch . '%');
            $this->db->bind('tanggal', $tanggal);
            $bs = $this->db->single();
            if ($bs) {
                $results['barcode_match_siswa'] = $bs;
            }
        }

        // 2. Cek jika menanyakan kelas tertentu (misal: "X RPL 1", "X RPL 2", "XI TKJ 1", atau "kelas")
        $this->db->query("SELECT id, nama_kelas FROM kelas");
        $allKelas = $this->db->resultSet();
        $matchedKelasId = null;
        $matchedKelasName = null;

        foreach ($allKelas as $k) {
            if (str_contains($q, strtolower($k['nama_kelas']))) {
                $matchedKelasId = $k['id'];
                $matchedKelasName = $k['nama_kelas'];

                // Detail kelas
                $this->db->query("
                    SELECT k.id, k.nama_kelas, COALESCE(g.nama, 'Belum ditentukan') as nama_wali, COALESCE(g.nip, '-') as nip_wali,
                           (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa
                    FROM kelas k
                    LEFT JOIN guru g ON k.wali_kelas_id = g.id
                    WHERE k.id = :kid
                ");
                $this->db->bind('kid', $k['id']);
                $kelasInfo = $this->db->single();

                // Hitung absensi kelas tersebut hari ini
                $this->db->query("
                    SELECT a.status, COUNT(*) as jml
                    FROM absensi_siswa a
                    JOIN siswa s ON a.siswa_id = s.id
                    WHERE s.kelas_id = :kid AND a.tanggal = :tanggal
                    GROUP BY a.status
                ");
                $this->db->bind('kid', $k['id']);
                $this->db->bind('tanggal', $tanggal);
                $absensiKelas = $this->db->resultSet();

                $rekap = ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
                foreach ($absensiKelas as $ak) {
                    $raw = $ak['status'];
                    $st = (int)$raw;
                    switch ($st) {
                        case 0: $rekap['hadir'] += (int)$ak['jml']; break;
                        case 1: $rekap['sakit'] += (int)$ak['jml']; break;
                        case 2: $rekap['izin']  += (int)$ak['jml']; break;
                        case 3: $rekap['alpa']  += (int)$ak['jml']; break;
                    }
                }

                // Ambil daftar siswa kelas tersebut jika ditanyakan
                $this->db->query("
                    SELECT s.nis, s.nama, s.barcode_id,
                           (SELECT status FROM absensi_siswa a WHERE a.siswa_id = s.id AND a.tanggal = :tanggal LIMIT 1) as status_hari_ini
                    FROM siswa s
                    WHERE s.kelas_id = :kid
                    ORDER BY s.nama ASC
                ");
                $this->db->bind('kid', $k['id']);
                $this->db->bind('tanggal', $tanggal);
                $daftarSiswaKelas = $this->db->resultSet();

                $results['kelas_spesifik'][] = [
                    'nama_kelas'     => $kelasInfo['nama_kelas'],
                    'nama_wali'      => $kelasInfo['nama_wali'],
                    'nip_wali'       => $kelasInfo['nip_wali'],
                    'total_siswa'    => (int)$kelasInfo['total_siswa'],
                    'rekap_hari_ini' => $rekap,
                    'daftar_siswa'   => $daftarSiswaKelas
                ];
                break;
            }
        }

        // 3. Pencarian Daftar Siswa Umum (jika menanyakan "semua siswa", "daftar siswa", "siapa saja siswa")
        if (str_contains($q, 'daftar siswa') || str_contains($q, 'semua siswa') || str_contains($q, 'siapa saja siswa')) {
            $this->db->query("
                SELECT s.nis, s.nama, k.nama_kelas, s.barcode_id
                FROM siswa s
                JOIN kelas k ON s.kelas_id = k.id
                ORDER BY k.nama_kelas ASC, s.nama ASC
            ");
            $allStudents = $this->db->resultSet();
            $grouped = [];
            foreach ($allStudents as $as) {
                $grouped[$as['nama_kelas']][] = "{$as['nama']} (NIS: {$as['nis']})";
            }
            $results['daftar_seluruh_siswa_per_kelas'] = $grouped;
        }

        // 4. Pencarian Siswa Spesifik (Berdasarkan Nama atau Angka NIS)
        $words = preg_split('/[\s,\.\?\!]+/', $query);
        $exclude = ['siapa', 'berapa', 'apakah', 'adakah', 'bagaimana', 'siswa', 'guru', 'kelas', 'jadwal', 'yang', 'hari', 'ini', 'dan', 'dari', 'pada', 'untuk', 'dengan', 'saya', 'data', 'absen', 'absensi', 'sudah', 'belum', 'ada', 'di', 'ke', 'kehadiran', 'daftar', 'semua', 'sekolah', 'milik', 'punya', 'saja', 'nama'];
        $potentialKeywords = [];
        foreach ($words as $w) {
            $w = trim(strtolower($w));
            if (strlen($w) >= 3 && !in_array($w, $exclude)) {
                $potentialKeywords[] = $w;
            }
        }

        if (!empty($potentialKeywords)) {
            $foundStudents = [];
            foreach (array_slice($potentialKeywords, 0, 3) as $kw) {
                $this->db->query("
                    SELECT s.nis, s.nama, s.barcode_id, k.nama_kelas, COALESCE(g.nama, 'Belum ditentukan') as nama_wali,
                           (SELECT status FROM absensi_siswa a WHERE a.siswa_id = s.id AND a.tanggal = :tanggal LIMIT 1) as status_hari_ini
                    FROM siswa s
                    LEFT JOIN kelas k ON s.kelas_id = k.id
                    LEFT JOIN guru g ON k.wali_kelas_id = g.id
                    WHERE LOWER(s.nama) LIKE :kw OR s.nis = :kw2
                    LIMIT 5
                ");
                $this->db->bind('kw', '%' . $kw . '%');
                $this->db->bind('kw2', $kw);
                $this->db->bind('tanggal', $tanggal);
                $rows = $this->db->resultSet();
                foreach ($rows as $r) {
                    $foundStudents[$r['nis']] = $r;
                }
            }
            if (!empty($foundStudents)) {
                $results['hasil_pencarian_siswa'] = array_values($foundStudents);
            }

            // Pencarian Guru Spesifik (Berdasarkan Nama atau NIP)
            $foundTeachers = [];
            foreach (array_slice($potentialKeywords, 0, 3) as $kw) {
                $this->db->query("
                    SELECT g.id, g.nip, g.nama, g.barcode_id,
                           (SELECT nama_kelas FROM kelas k WHERE k.wali_kelas_id = g.id LIMIT 1) as wali_kelas,
                           (SELECT status FROM absensi_guru ag WHERE ag.guru_id = g.id AND ag.tanggal = :tanggal LIMIT 1) as status_hari_ini
                    FROM guru g
                    WHERE LOWER(g.nama) LIKE :kw OR g.nip = :kw2
                    LIMIT 3
                ");
                $this->db->bind('kw', '%' . $kw . '%');
                $this->db->bind('kw2', $kw);
                $this->db->bind('tanggal', $tanggal);
                $tRows = $this->db->resultSet();
                foreach ($tRows as $tr) {
                    $foundTeachers[$tr['nip']] = $tr;
                }
            }
            if (!empty($foundTeachers)) {
                $results['hasil_pencarian_guru'] = array_values($foundTeachers);
            }
        }

        // 5. Pencarian Jadwal Pelajaran (Hari, Kelas, atau Guru)
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $hariFilter = null;
        foreach ($hariList as $h) {
            if (str_contains($q, strtolower($h))) {
                $hariFilter = $h;
                break;
            }
        }

        if (str_contains($q, 'jadwal') || $hariFilter) {
            $hariAktif = $this->getHariIndo($tanggal);
            $targetHari = $hariFilter ?? (str_contains($q, 'hari ini') ? $hariAktif : null);

            $sqlJadwal = "
                SELECT j.hari, j.mata_pelajaran, j.jam_mulai, j.jam_selesai, k.nama_kelas, g.nama as nama_guru
                FROM jadwal j
                JOIN kelas k ON j.kelas_id = k.id
                JOIN guru g ON j.guru_id = g.id
                WHERE 1=1
            ";
            $params = [];
            if ($targetHari) {
                $sqlJadwal .= " AND j.hari = :hari";
                $params['hari'] = $targetHari;
            }
            if ($matchedKelasId) {
                $sqlJadwal .= " AND j.kelas_id = :kid";
                $params['kid'] = $matchedKelasId;
            }

            // Jika ada pencarian guru spesifik
            if (!empty($results['hasil_pencarian_guru'])) {
                $firstGuru = $results['hasil_pencarian_guru'][0];
                $sqlJadwal .= " AND j.guru_id = :gid";
                $params['gid'] = $firstGuru['id'];
            }

            $sqlJadwal .= " ORDER BY j.hari ASC, k.nama_kelas ASC, j.jam_mulai ASC LIMIT 30";
            $this->db->query($sqlJadwal);
            foreach ($params as $pk => $pv) {
                $this->db->bind($pk, $pv);
            }
            $jadwalResult = $this->db->resultSet();
            if (!empty($jadwalResult)) {
                $results['jadwal_pencarian'] = $jadwalResult;
            }
        }

        // 6. Pencarian Status Kehadiran (Sakit, Izin, Alpa, Terlambat, Belum Absen, Hadir)
        $statusCheck = null;
        if (str_contains($q, 'sakit')) $statusCheck = 'sakit';
        elseif (str_contains($q, 'izin')) $statusCheck = 'izin';
        elseif (str_contains($q, 'alpa') || str_contains($q, 'alpha')) $statusCheck = 'alpa';
        elseif (str_contains($q, 'terlambat')) $statusCheck = 'terlambat';
        elseif (str_contains($q, 'belum absen') || str_contains($q, 'tidak hadir') || str_contains($q, 'belum hadir')) $statusCheck = 'belum';
        elseif (str_contains($q, 'sudah hadir') || str_contains($q, 'sudah absen') || str_contains($q, 'yang hadir')) $statusCheck = 'hadir';

        if ($statusCheck) {
            if ($statusCheck === 'belum') {
                $this->db->query("
                    SELECT s.nama, s.nis, k.nama_kelas
                    FROM siswa s
                    LEFT JOIN kelas k ON s.kelas_id = k.id
                    WHERE s.id NOT IN (SELECT siswa_id FROM absensi_siswa WHERE tanggal = :tanggal)
                    ORDER BY k.nama_kelas ASC, s.nama ASC
                    LIMIT 25
                ");
                $this->db->bind('tanggal', $tanggal);
                $results['daftar_belum_absen'] = $this->db->resultSet();
            } else {
                $statusMapSiswa = [
                    'hadir'     => 0,
                    'sakit'     => 1,
                    'izin'      => 2,
                    'alpa'      => 3,
                ];

                if (isset($statusMapSiswa[$statusCheck])) {
                    $this->db->query("
                        SELECT s.nama, s.nis, k.nama_kelas, a.jam, a.status
                        FROM absensi_siswa a
                        JOIN siswa s ON a.siswa_id = s.id
                        LEFT JOIN kelas k ON s.kelas_id = k.id
                        WHERE a.tanggal = :tanggal AND a.status = :status
                        ORDER BY s.nama ASC
                        LIMIT 25
                    ");
                    $this->db->bind('tanggal', $tanggal);
                    $this->db->bind('status', $statusMapSiswa[$statusCheck]);
                    $results['daftar_status_' . $statusCheck] = $this->db->resultSet();
                }

                // Juga cari Guru dengan status tersebut di absensi_guru
                $this->db->query("
                    SELECT g.nama, g.nip, ag.jam_masuk, ag.jam_pulang, ag.status
                    FROM absensi_guru ag
                    JOIN guru g ON ag.guru_id = g.id
                    WHERE ag.tanggal = :tanggal AND ag.status = :status
                    ORDER BY g.nama ASC
                    LIMIT 25
                ");
                $this->db->bind('tanggal', $tanggal);
                $this->db->bind('status', $statusCheck);
                $results['daftar_guru_status_' . $statusCheck] = $this->db->resultSet();
            }
        }

        // 7. Info Kepala Sekolah / Pimpinan
        if (str_contains($q, 'kepala sekolah') || str_contains($q, 'kepsek') || str_contains($q, 'pimpinan')) {
            $this->db->query("SELECT id, username, role FROM users WHERE role = 'kepala_sekolah'");
            $results['kepala_sekolah'] = $this->db->resultSet();
        }

        return $results;
    }

    /**
     * Membangun string konteks database lengkap dan terstruktur secara real-time
     * untuk diinjeksikan langsung ke dalam AI System Prompt.
     * Mengandung seluruh data master sekolah sehingga AI dapat menjawab SEMUA pertanyaan database.
     */
    public function buildDatabaseContext(int $userId, string $role, string $username, string $userMessage = ''): string {
        $tanggal = date('Y-m-d');
        $hari    = $this->getHariIndo($tanggal);
        $jam     = date('H:i');

        $lines = [];
        $lines[] = "=== DATA RESMI DATABASE SISTEM ABSENSI (REAL-TIME MYSQL) ===";
        $lines[] = "Waktu Server: {$tanggal} (Hari {$hari}), Pukul {$jam} WIB";
        $lines[] = "Akun Login Anda: {$username} | Role: " . strtoupper($role);
        $lines[] = "";

        // 1. Data Diri Pengguna yang Sedang Login & Data Kehadiran Pribadi
        $lines[] = "[DATA DIRI PENGGUNA YANG LOGIN]";
        switch ($role) {
            case 'siswa':
                $sc = $this->getSiswaContext($userId, $tanggal, $hari);
                if ($sc && $sc['profil']) {
                    $p = $sc['profil'];
                    $lines[] = "- Nama Lengkap: {$p['nama']}";
                    $lines[] = "- NIS: {$p['nis']}";
                    $lines[] = "- Kelas: " . ($p['nama_kelas'] ?? 'Belum ada kelas');
                    $lines[] = "- Wali Kelas: {$p['nama_wali']}";
                    $lines[] = "- Barcode ID: " . ($p['barcode_id'] ?? 'Belum ada barcode');
                    $lines[] = "";

                    $lines[] = "[STATUS KEHADIRAN PRIBADI HARI INI]";
                    $ab = $sc['absen_hari_ini'];
                    if ($ab) {
                        $st = $this->formatStatusSiswa($ab['status']);
                        $jam = substr($ab['jam'], 0, 5);
                        $lines[] = "- Status Absensi Hari Ini: SUDAH ABSEN ({$st} Pukul {$jam} WIB)";
                    } else {
                        $lines[] = "- Status Absensi Hari Ini: BELUM MELAKUKAN ABSENSI HARI INI ({$tanggal}).";
                    }
                    $lines[] = "";

                    $lines[] = "[REKAPITULASI KEHADIRAN PRIBADI DARI DATABASE]";
                    if ($sc['rekap']) {
                        $rk = $sc['rekap'];
                        $tot = (int)$rk['total_catatan'];
                        $hdr = (int)$rk['hadir'];
                        $skt = (int)$rk['sakit'];
                        $izn = (int)$rk['izin'];
                        $alp = (int)$rk['alpa'];
                        $pct = $tot > 0 ? round(($hdr / $tot) * 100, 1) . '%' : '0%';
                        $lines[] = "- Total Hadir: {$hdr} hari";
                        $lines[] = "- Total Sakit: {$skt} hari";
                        $lines[] = "- Total Izin: {$izn} hari";
                        $lines[] = "- Total Alpha: {$alp} hari";
                        $lines[] = "- Total Hari Tercatat: {$tot} hari (Persentase Kehadiran: {$pct})";
                    }
                    $lines[] = "";

                    $lines[] = "[RIWAYAT KEHADIRAN PRIBADI TERAKHIR]";
                    if (!empty($sc['riwayat'])) {
                        foreach ($sc['riwayat'] as $rw) {
                            $st = $this->formatStatusSiswa($rw['status']);
                            $hIndo = $this->getHariIndo($rw['tanggal']);
                            $lines[] = "  * {$hIndo}, {$rw['tanggal']} - Pukul " . substr($rw['jam'], 0, 5) . " WIB | Status: {$st}";
                        }
                    } else {
                        $lines[] = "  - Belum ada riwayat kehadiran.";
                    }
                    $lines[] = "";

                    $lines[] = "[JADWAL PELAJARAN KELAS HARI INI (HARI {$hari})]";
                    if (!empty($sc['jadwal_hari_ini'])) {
                        foreach ($sc['jadwal_hari_ini'] as $jd) {
                            $jm = substr($jd['jam_mulai'], 0, 5);
                            $js = substr($jd['jam_selesai'], 0, 5);
                            $mapel = !empty($jd['mata_pelajaran']) ? $jd['mata_pelajaran'] : 'Mata Pelajaran';
                            $lines[] = "  * Pukul {$jm} - {$js} WIB | Mapel: {$mapel} | Guru: {$jd['nama_guru']}";
                        }
                    } else {
                        $lines[] = "  - Tidak ada jadwal pelajaran hari ini.";
                    }
                }
                break;

            case 'guru':
            case 'wali_kelas':
                $gc = $this->getGuruContext($userId, $tanggal, $hari);
                if ($gc && $gc['profil']) {
                    $g = $gc['profil'];
                    $wk = !empty($gc['kelas_wali']['nama_kelas']) ? "Wali Kelas {$gc['kelas_wali']['nama_kelas']}" : "Guru Mata Pelajaran";
                    $lines[] = "- Nama: {$g['nama']}";
                    $lines[] = "- NIP: {$g['nip']}";
                    $lines[] = "- Barcode ID: " . ($g['barcode_id'] ?? 'Belum ada barcode');
                    $lines[] = "- Tugas: {$wk}";
                    $lines[] = "";

                    $lines[] = "[STATUS KEHADIRAN PRIBADI HARI INI]";
                    $ag = $gc['absen_hari_ini'];
                    if ($ag) {
                        $st = $this->formatStatusGuru($ag['status']);
                        $jm = substr($ag['jam_masuk'], 0, 5);
                        $jp = !empty($ag['jam_pulang']) ? substr($ag['jam_pulang'], 0, 5) . " WIB" : "Belum scan pulang";
                        $lines[] = "- Status Absensi Hari Ini: SUDAH ABSEN ({$st} | Masuk: {$jm} WIB, Pulang: {$jp})";
                    } else {
                        $lines[] = "- Status Absensi Hari Ini: BELUM MELAKUKAN ABSENSI MASUK HARI INI ({$tanggal}).";
                    }
                    $lines[] = "";

                    $lines[] = "[REKAPITULASI KEHADIRAN PRIBADI DARI DATABASE]";
                    if ($gc['rekap']) {
                        $rk = $gc['rekap'];
                        $tot = (int)$rk['total_catatan'];
                        $hdr = (int)$rk['hadir'];
                        $tlb = (int)$rk['terlambat'];
                        $skt = (int)$rk['sakit'];
                        $izn = (int)$rk['izin'];
                        $alp = (int)$rk['alpa'];
                        $lines[] = "- Total Hadir (Tepat Waktu): {$hdr} hari";
                        $lines[] = "- Total Hadir (Terlambat): {$tlb} hari";
                        $lines[] = "- Total Sakit: {$skt} hari";
                        $lines[] = "- Total Izin: {$izn} hari";
                        $lines[] = "- Total Alpha: {$alp} hari";
                        $lines[] = "- Total Hari Tercatat: {$tot} hari";
                    }
                    $lines[] = "";

                    $lines[] = "[RIWAYAT KEHADIRAN PRIBADI TERAKHIR]";
                    if (!empty($gc['riwayat'])) {
                        foreach ($gc['riwayat'] as $rw) {
                            $st = $this->formatStatusGuru($rw['status']);
                            $hIndo = $this->getHariIndo($rw['tanggal']);
                            $jm = substr($rw['jam_masuk'], 0, 5);
                            $jp = !empty($rw['jam_pulang']) ? substr($rw['jam_pulang'], 0, 5) . " WIB" : "-";
                            $lines[] = "  * {$hIndo}, {$rw['tanggal']} | Masuk: {$jm} WIB, Pulang: {$jp} | Status: {$st}";
                        }
                    } else {
                        $lines[] = "  - Belum ada riwayat kehadiran guru.";
                    }
                    $lines[] = "";

                    $lines[] = "[JADWAL MENGAJAR HARI INI (HARI {$hari})]";
                    if (!empty($gc['jadwal_hari_ini'])) {
                        foreach ($gc['jadwal_hari_ini'] as $jd) {
                            $jm = substr($jd['jam_mulai'], 0, 5);
                            $js = substr($jd['jam_selesai'], 0, 5);
                            $mapel = !empty($jd['mata_pelajaran']) ? $jd['mata_pelajaran'] : 'Mata Pelajaran';
                            $lines[] = "  * Pukul {$jm} - {$js} WIB | Mapel: {$mapel} | Kelas: {$jd['nama_kelas']}";
                        }
                    } else {
                        $lines[] = "  - Tidak ada jadwal mengajar hari ini.";
                    }

                    // Jika merangkap wali kelas
                    if ($gc['kelas_wali']) {
                        $lines[] = "";
                        $wkData = $this->getWaliKelasContext($userId, $tanggal);
                        if ($wkData && !empty($wkData['kelas_wali'])) {
                            $rkK = $wkData['rekap_kelas'];
                            $lines[] = "[REKAPITULASI KELAS BINAAN HARI INI (KELAS {$wkData['kelas_wali']['nama_kelas']})]";
                            $lines[] = "- Total Siswa: {$wkData['total_siswa']} siswa";
                            $lines[] = "- Hadir: {$rkK['hadir']} | Sakit: {$rkK['sakit']} | Izin: {$rkK['izin']} | Alpha: {$rkK['alpa']} | Belum Absen: {$rkK['belum_absen']}";
                            if (!empty($wkData['daftar_absen'])) {
                                $lines[] = "- Siswa Tidak Hadir / Belum Absen Hari Ini:";
                                foreach ($wkData['daftar_absen'] as $da) {
                                    $lines[] = "  * {$da}";
                                }
                            }
                        }
                    }
                }
                break;

            case 'kepala_sekolah':
                $lines[] = "- Username: {$username}";
                $lines[] = "- Jabatan: Kepala Sekolah";
                $lines[] = "- Wewenang: Memantau seluruh laporan dan statistik kehadiran siswa dan guru sekolah.";
                break;

            case 'admin':
            default:
                $lines[] = "- Username: {$username}";
                $lines[] = "- Jabatan: Administrator Sistem Absensi";
                $lines[] = "- Wewenang: Akses penuh untuk mengelola data siswa, guru, kelas, user, barcode, dan jadwal, serta melihat seluruh data dan statistik sekolah.";
                break;
        }
        $lines[] = "";

        // 2. Statistik Master Data Sekolah Lengkap
        $this->db->query("SELECT COUNT(*) as total FROM siswa");
        $totSiswa = (int)$this->db->single()['total'];
        $this->db->query("SELECT COUNT(*) as total FROM guru");
        $totGuru = (int)$this->db->single()['total'];
        $this->db->query("SELECT COUNT(*) as total FROM kelas");
        $totKelas = (int)$this->db->single()['total'];
        $this->db->query("SELECT COUNT(*) as total FROM users");
        $totUser = (int)$this->db->single()['total'];

        $this->db->query("SELECT username FROM users WHERE role = 'kepala_sekolah'");
        $kepseks = array_column($this->db->resultSet(), 'username');
        $this->db->query("SELECT username FROM users WHERE role = 'admin'");
        $admins = array_column($this->db->resultSet(), 'username');

        $lines[] = "[RINGKASAN MASTER DATA SEKOLAH DARI DATABASE]";
        $lines[] = "- Total Siswa Terdaftar: {$totSiswa} siswa";
        $lines[] = "- Total Guru Terdaftar: {$totGuru} guru";
        $lines[] = "- Total Rombongan Belajar (Kelas): {$totKelas} kelas";
        $lines[] = "- Total Akun Pengguna (Users): {$totUser} akun";
        $lines[] = "- Kepala Sekolah Tercatat: " . (!empty($kepseks) ? implode(', ', $kepseks) : 'Belum diset');
        $lines[] = "- Administrator Sistem: " . (!empty($admins) ? implode(', ', $admins) : 'admin');
        $lines[] = "";

        // 3. Data Seluruh Kelas & Wali Kelas
        $this->db->query("
            SELECT k.id, k.nama_kelas, COALESCE(g.nama, 'Belum ditentukan') as nama_wali, COALESCE(g.nip, '-') as nip_wali,
                   (SELECT COUNT(*) FROM siswa s WHERE s.kelas_id = k.id) as total_siswa
            FROM kelas k
            LEFT JOIN guru g ON k.wali_kelas_id = g.id
            ORDER BY k.id ASC
        ");
        $kelasList = $this->db->resultSet();
        $lines[] = "[DAFTAR SELURUH KELAS DI SEKOLAH DARI DATABASE]";
        foreach ($kelasList as $kl) {
            $lines[] = "  * Kelas {$kl['nama_kelas']}: Wali Kelas {$kl['nama_wali']} (NIP: {$kl['nip_wali']}) | Total Siswa: {$kl['total_siswa']} orang";
        }
        $lines[] = "";

        // 4. Data Seluruh Guru di Database
        $this->db->query("
            SELECT g.id, g.nip, g.nama, g.barcode_id,
                   (SELECT nama_kelas FROM kelas k WHERE k.wali_kelas_id = g.id LIMIT 1) as wali_kelas,
                   (SELECT status FROM absensi_guru ag WHERE ag.guru_id = g.id AND ag.tanggal = '{$tanggal}' LIMIT 1) as status_hari_ini
            FROM guru g
            ORDER BY g.id ASC
        ");
        $guruList = $this->db->resultSet();
        $lines[] = "[DAFTAR SELURUH GURU DI DATABASE SEKOLAH]";
        foreach ($guruList as $gr) {
            $wkText = $gr['wali_kelas'] ? " (Wali Kelas {$gr['wali_kelas']})" : "";
            $stText = $gr['status_hari_ini'] ? "Status Hari Ini: " . strtoupper($gr['status_hari_ini']) : "Status Hari Ini: Belum Absen";
            $lines[] = "  * {$gr['nama']} | NIP: {$gr['nip']} | Barcode: {$gr['barcode_id']}{$wkText} | {$stText}";
        }
        $lines[] = "";

        // 5. Rekapitulasi Kehadiran Hari Ini
        $this->db->query("SELECT status, COUNT(*) as jml FROM absensi_siswa WHERE tanggal = '{$tanggal}' GROUP BY status");
        $rekapSiswaRows = $this->db->resultSet();
        $rkSiswa = ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
        foreach ($rekapSiswaRows as $rs) {
            $raw = $rs['status'];
            $st = (int)$raw;
            switch ($st) {
                case 0: $rkSiswa['hadir'] += (int)$rs['jml']; break;
                case 1: $rkSiswa['sakit'] += (int)$rs['jml']; break;
                case 2: $rkSiswa['izin']  += (int)$rs['jml']; break;
                case 3: $rkSiswa['alpa']  += (int)$rs['jml']; break;
            }
        }

        $rkGuru = $this->getRekapGuruHariIni($tanggal);

        $lines[] = "[REKAPITULASI KEHADIRAN HARI INI ({$tanggal})]";
        $lines[] = "- Siswa Hadir: {$rkSiswa['hadir']} | Terlambat: {$rkSiswa['terlambat']} | Sakit: {$rkSiswa['sakit']} | Izin: {$rkSiswa['izin']} | Alpa: {$rkSiswa['alpa']}";
        $lines[] = "- Guru Hadir (Tepat Waktu): {$rkGuru['hadir']} | Terlambat: {$rkGuru['terlambat']} | Sakit: {$rkGuru['sakit']} | Izin: {$rkGuru['izin']} | Alpa: {$rkGuru['alpa']} (Total Guru: {$totGuru} guru)";
        $lines[] = "";

        // 6. Data Tambahan Hasil Pencarian Dinamis Terkait Pertanyaan Pengguna
        if (!empty($userMessage)) {
            $searchResults = $this->searchDatabase($userMessage, $role, $userId, $tanggal);

            // A. Hasil Pencarian Barcode
            if (!empty($searchResults['barcode_match_guru'])) {
                $bg = $searchResults['barcode_match_guru'];
                $lines[] = "[HASIL PENCARIAN BARCODE]";
                $lines[] = "  * Pemilik Barcode: Guru {$bg['nama']} (NIP: {$bg['nip']}, Barcode: {$bg['barcode_id']})";
                $lines[] = "";
            }
            if (!empty($searchResults['barcode_match_siswa'])) {
                $bs = $searchResults['barcode_match_siswa'];
                $lines[] = "[HASIL PENCARIAN BARCODE]";
                $st = $this->formatStatusSiswa($bs['status_hari_ini']);
                $lines[] = "  * Pemilik Barcode: Siswa {$bs['nama']} (NIS: {$bs['nis']}, Kelas: {$bs['nama_kelas']}, Barcode: {$bs['barcode_id']}, Status: {$st})";
                $lines[] = "";
            }

            // B. Detail Kelas Spesifik & Daftar Siswa Kelas
            if (!empty($searchResults['kelas_spesifik'])) {
                foreach ($searchResults['kelas_spesifik'] as $ks) {
                    $lines[] = "[INFORMASI KELAS {$ks['nama_kelas']} DARI DATABASE]";
                    $lines[] = "- Nama Kelas: {$ks['nama_kelas']} | Wali Kelas: {$ks['nama_wali']} (NIP: {$ks['nip_wali']}) | Total Siswa: {$ks['total_siswa']} siswa";
                    $rk = $ks['rekap_hari_ini'];
                    $lines[] = "- Kehadiran Hari Ini: Hadir: {$rk['hadir']}, Terlambat: {$rk['terlambat']}, Sakit: {$rk['sakit']}, Izin: {$rk['izin']}, Alpa: {$rk['alpa']}";
                    if (!empty($ks['daftar_siswa'])) {
                        $lines[] = "- Daftar Siswa di Kelas {$ks['nama_kelas']}:";
                        foreach ($ks['daftar_siswa'] as $dsk) {
                            $st = $this->formatStatusSiswa($dsk['status_hari_ini']);
                            $lines[] = "  * {$dsk['nama']} (NIS: {$dsk['nis']}) | Barcode: {$dsk['barcode_id']} | Status: {$st}";
                        }
                    }
                    $lines[] = "";
                }
            }

            // C. Daftar Seluruh Siswa per Kelas
            if (!empty($searchResults['daftar_seluruh_siswa_per_kelas'])) {
                $lines[] = "[DAFTAR SELURUH SISWA PER KELAS]";
                foreach ($searchResults['daftar_seluruh_siswa_per_kelas'] as $knama => $sList) {
                    $lines[] = "* Kelas {$knama} (" . count($sList) . " siswa):";
                    foreach ($sList as $item) {
                        $lines[] = "  - {$item}";
                    }
                }
                $lines[] = "";
            }

            // D. Hasil Pencarian Siswa Spesifik
            if (!empty($searchResults['hasil_pencarian_siswa'])) {
                $lines[] = "[HASIL PENCARIAN SISWA DARI DATABASE]";
                foreach ($searchResults['hasil_pencarian_siswa'] as $hs) {
                    $st = $this->formatStatusSiswa($hs['status_hari_ini']);
                    $lines[] = "  * {$hs['nama']} | NIS: {$hs['nis']} | Kelas: {$hs['nama_kelas']} | Wali Kelas: {$hs['nama_wali']} | Barcode: {$hs['barcode_id']} | Status Kehadiran Hari Ini: {$st}";
                }
                $lines[] = "";
            }

            // E. Hasil Pencarian Guru Spesifik
            if (!empty($searchResults['hasil_pencarian_guru'])) {
                $lines[] = "[HASIL PENCARIAN GURU DARI DATABASE]";
                foreach ($searchResults['hasil_pencarian_guru'] as $hg) {
                    $wk = $hg['wali_kelas'] ? "Wali Kelas {$hg['wali_kelas']}" : "Guru Mata Pelajaran";
                    $st = $this->formatStatusGuru($hg['status_hari_ini']);
                    $lines[] = "  * {$hg['nama']} | NIP: {$hg['nip']} | Tugas: {$wk} | Barcode: {$hg['barcode_id']} | Status: {$st}";
                }
                $lines[] = "";
            }

            // F. Jadwal Pelajaran
            if (!empty($searchResults['jadwal_pencarian'])) {
                $lines[] = "[DATA JADWAL PELAJARAN DARI DATABASE]";
                foreach ($searchResults['jadwal_pencarian'] as $jp) {
                    $mapel = !empty($jp['mata_pelajaran']) ? $jp['mata_pelajaran'] : 'Mata Pelajaran';
                    $lines[] = "  * Hari {$jp['hari']} | Pukul {$jp['jam_mulai']} - {$jp['jam_selesai']} WIB | Mapel: {$mapel} | Kelas {$jp['nama_kelas']} | Guru: {$jp['nama_guru']}";
                }
                $lines[] = "";
            }

            // G. Siswa Belum Absen
            if (!empty($searchResults['daftar_belum_absen'])) {
                $lines[] = "[DAFTAR SISWA BELUM MELAKUKAN ABSENSI HARI INI ({$tanggal})]";
                foreach ($searchResults['daftar_belum_absen'] as $ba) {
                    $lines[] = "  * {$ba['nama']} (NIS: {$ba['nis']}) - Kelas {$ba['nama_kelas']}";
                }
                $lines[] = "";
            }

            // H. Status Absensi Spesifik
            foreach ($searchResults as $k => $val) {
                if (str_starts_with($k, 'daftar_status_') && is_array($val)) {
                    $stName = strtoupper(str_replace('daftar_status_', '', $k));
                    $lines[] = "[DAFTAR SISWA DENGAN STATUS {$stName} HARI INI ({$tanggal})]";
                    if (empty($val)) {
                        $lines[] = "  - Tidak ada siswa dengan status {$stName} hari ini.";
                    } else {
                        foreach ($val as $ds) {
                            $lines[] = "  * {$ds['nama']} (NIS: {$ds['nis']}) - Kelas {$ds['nama_kelas']} (Pukul {$ds['jam']})";
                        }
                    }
                    $lines[] = "";
                }

                if (str_starts_with($k, 'daftar_guru_status_') && is_array($val)) {
                    $stName = strtoupper(str_replace('daftar_guru_status_', '', $k));
                    $lines[] = "[DAFTAR GURU DENGAN STATUS {$stName} HARI INI ({$tanggal})]";
                    if (empty($val)) {
                        $lines[] = "  - Tidak ada guru dengan status {$stName} hari ini.";
                    } else {
                        foreach ($val as $dg) {
                            $lines[] = "  * {$dg['nama']} (NIP: {$dg['nip']}) - Masuk: " . substr($dg['jam_masuk'], 0, 5) . " WIB";
                        }
                    }
                    $lines[] = "";
                }
            }

            // I. Kepala Sekolah
            if (!empty($searchResults['kepala_sekolah'])) {
                $lines[] = "[DATA KEPALA SEKOLAH DARI DATABASE]";
                foreach ($searchResults['kepala_sekolah'] as $ksk) {
                    $lines[] = "  * Username: {$ksk['username']} | Role: {$ksk['role']}";
                }
                $lines[] = "";
            }
        }

        $lines[] = "============================================================";
        return implode("\n", $lines);
    }
}
