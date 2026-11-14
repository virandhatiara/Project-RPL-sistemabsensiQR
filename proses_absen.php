<?php
/**
 * proses_absen.php
 * File Backend Pemroses Presensi Siswa & Guru via Scanner QR Code / Barcode (Kamera / USB)
 * Terintegrasi dengan Bot Telegram @AbsensiSmkNuBot untuk notifikasi real-time.
 * 
 * Aturan Nilai Status Integer (TINYINT):
 * 0 = Hadir
 * 1 = Sakit
 * 2 = Izin
 * 3 = Alpha
 * 
 * Query SQL:
 * ALTER TABLE `absensi_siswa` MODIFY COLUMN `status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Hadir, 1=Sakit, 2=Izin, 3=Alpha';
 */

// 1. Header JSON & Pengaturan Timezone
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Set timezone ke Waktu Indonesia Barat (WIB)
date_default_timezone_set('Asia/Jakarta');

// Muat konfigurasi .env dan konstanta jika tersedia
if (file_exists(__DIR__ . '/config/config.php')) {
    require_once __DIR__ . '/config/config.php';
} elseif (file_exists(__DIR__ . '/../config/config.php')) {
    require_once __DIR__ . '/../config/config.php';
}

// Pastikan hanya method POST yang diproses
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Metode request tidak diizinkan. Gunakan method POST.'
    ]);
    exit;
}

// 2. Ambil Input (Mendukung Form-Data, X-WWW-Form-Urlencoded, maupun JSON Body)
$code = '';
$rawJson = null;

// Coba baca dari raw JSON body terlebih dahulu jika ada
$raw = file_get_contents('php://input');
if (!empty($raw)) {
    $rawJson = json_decode($raw, true);
}

$action = $_POST['action'] ?? ($rawJson['action'] ?? '');

if (isset($_POST['barcode_id']) && trim($_POST['barcode_id']) !== '') {
    $code = trim($_POST['barcode_id']);
} elseif (isset($_POST['nis']) && trim($_POST['nis']) !== '') {
    $code = trim($_POST['nis']);
} elseif (isset($_POST['nip']) && trim($_POST['nip']) !== '') {
    $code = trim($_POST['nip']);
} elseif (isset($_POST['kode']) && trim($_POST['kode']) !== '') {
    $code = trim($_POST['kode']);
} elseif (is_array($rawJson)) {
    $code = trim($rawJson['barcode_id'] ?? $rawJson['nis'] ?? $rawJson['nip'] ?? $rawJson['kode'] ?? '');
}

if ($action !== 'auto_alpha' && empty($code)) {
    http_response_code(400);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Kode Barcode / QR / NIS / NIP kosong atau tidak terbaca.'
    ]);
    exit;
}

// 3. Tangkap Parameter Status Kehadiran berupa Angka (Integer)
// 0 = Hadir, 1 = Sakit, 2 = Izin, 3 = Alpha
$statusInput = 0; // Default: Hadir (0)

if (isset($_POST['status']) && trim((string)$_POST['status']) !== '') {
    $rawStatus = trim((string)$_POST['status']);
    if (is_numeric($rawStatus)) {
        $statusInput = (int)$rawStatus;
    } else {
        // Fallback jika sistem lama mengirim teks string
        $sLower = strtolower($rawStatus);
        if ($sLower === 'sakit') $statusInput = 1;
        elseif ($sLower === 'izin') $statusInput = 2;
        elseif ($sLower === 'alpa' || $sLower === 'alpha') $statusInput = 3;
        else $statusInput = 0;
    }
} elseif (is_array($rawJson) && isset($rawJson['status']) && trim((string)$rawJson['status']) !== '') {
    $rawStatus = trim((string)$rawJson['status']);
    if (is_numeric($rawStatus)) {
        $statusInput = (int)$rawStatus;
    } else {
        $sLower = strtolower($rawStatus);
        if ($sLower === 'sakit') $statusInput = 1;
        elseif ($sLower === 'izin') $statusInput = 2;
        elseif ($sLower === 'alpa' || $sLower === 'alpha') $statusInput = 3;
        else $statusInput = 0;
    }
}

// Validasi rentang nilai integer status
if (!in_array($statusInput, [0, 1, 2, 3], true)) {
    $statusInput = 0;
}

// 4. Konfigurasi Koneksi Database MySQL
$dbHost = 'localhost';
$dbName = 'db_sistemabsensi';
$dbUser = 'root';
$dbPass = '';

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Gagal terhubung ke database: ' . $e->getMessage()
    ]);
    exit;
}

$tanggalHariIni = date('Y-m-d');
$jamSekarang    = date('H:i:s');

try {
    // =========================================================================
    // OTOMATIS DATA ALPHA SISWA & GURU YANG BELUM ABSEN (HADIR/SAKIT/IZIN)
    // =========================================================================
    $targetTanggal = (!empty($_POST['tanggal'])) ? trim($_POST['tanggal']) : ((!empty($rawJson['tanggal'])) ? trim($rawJson['tanggal']) : $tanggalHariIni);

    // 1. Siswa yang belum absen langsung dicatat sebagai Alpha (status = 3)
    $stmtSiswaAlpha = $pdo->prepare("
        INSERT INTO absensi_siswa (siswa_id, tanggal, jam, status)
        SELECT s.id, :tanggal, '07:00:00', 3
        FROM siswa s
        WHERE s.id NOT IN (
            SELECT a.siswa_id FROM absensi_siswa a WHERE a.tanggal = :tanggal_cek
        )
    ");
    $stmtSiswaAlpha->execute(['tanggal' => $targetTanggal, 'tanggal_cek' => $targetTanggal]);
    $countSiswaAlpha = $stmtSiswaAlpha->rowCount();

    // 2. Guru yang belum absen langsung dicatat sebagai Alpha (status = 'alpa')
    $stmtGuruAlpha = $pdo->prepare("
        INSERT INTO absensi_guru (guru_id, tanggal, jam_masuk, status)
        SELECT g.id, :tanggal, '07:00:00', 'alpa'
        FROM guru g
        WHERE g.id NOT IN (
            SELECT ag.guru_id FROM absensi_guru ag WHERE ag.tanggal = :tanggal_cek
        )
    ");
    $stmtGuruAlpha->execute(['tanggal' => $targetTanggal, 'tanggal_cek' => $targetTanggal]);
    $countGuruAlpha = $stmtGuruAlpha->rowCount();

    // Jika request khusus action auto_alpha, langsung berikan response
    if ($action === 'auto_alpha') {
        echo json_encode([
            'status'                  => 'success',
            'message'                 => 'Sinkronisasi data Alpha otomatis selesai.',
            'tanggal'                 => $targetTanggal,
            'siswa_alpha_ditambahkan' => $countSiswaAlpha,
            'guru_alpha_ditambahkan'  => $countGuruAlpha
        ]);
        exit;
    }
    // 5. Cari Data Siswa Berdasarkan barcode_id ATAU nis
    $stmtSiswa = $pdo->prepare("
        SELECT s.id, s.nis, s.nama, s.barcode_id, s.kelas_id, s.no_wa, s.nama_ortu, s.no_hp_ortu, k.nama_kelas
        FROM siswa s
        LEFT JOIN kelas k ON s.kelas_id = k.id
        WHERE s.barcode_id = :code1 OR s.nis = :code2
        LIMIT 1
    ");
    $stmtSiswa->execute(['code1' => $code, 'code2' => $code]);
    $siswa = $stmtSiswa->fetch();

    if ($siswa) {
        // === PROSES PRESENSI SISWA ===
        $stmtCek = $pdo->prepare("
            SELECT id, jam, status, tanggal
            FROM absensi_siswa
            WHERE siswa_id = :siswa_id AND tanggal = :tanggal
            LIMIT 1
        ");
        $stmtCek->execute([
            'siswa_id' => $siswa['id'],
            'tanggal'  => $tanggalHariIni
        ]);
        $absenAda = $stmtCek->fetch();

        $isUpdateAlpha = false;
        if ($absenAda) {
            if ((int)$absenAda['status'] === 3) {
                // Siswa sebelumnya terdata Alpha otomatis, kini scan untuk memperbarui ke status yang dipilih (Hadir/Sakit/Izin)
                $isUpdateAlpha = true;
                $stmtUpdate = $pdo->prepare("
                    UPDATE absensi_siswa 
                    SET jam = :jam, status = :status 
                    WHERE id = :id
                ");
                $querySukses = $stmtUpdate->execute([
                    'jam'    => $jamSekarang,
                    'status' => $statusInput,
                    'id'     => $absenAda['id']
                ]);
            } else {
                // Logika konversi angka status ke teks untuk tampilan
                switch ((int)$absenAda['status']) {
                    case 1:
                        $labelStatusLama = 'Sakit';
                        break;
                    case 2:
                        $labelStatusLama = 'Izin';
                        break;
                    case 3:
                        $labelStatusLama = 'Alpha';
                        break;
                    case 0:
                    default:
                        $labelStatusLama = 'Hadir';
                        break;
                }

                echo json_encode([
                    'status'     => 'warning',
                    'keterangan' => 'Sudah Absen',
                    'message'    => 'Keterangan: Siswa sudah melakukan absensi hari ini dengan status "' . $labelStatusLama . '" pada pukul ' . substr($absenAda['jam'], 0, 5) . ' WIB.',
                    'data'       => [
                        'role'        => 'Siswa',
                        'nis'         => $siswa['nis'],
                        'nama'        => $siswa['nama'],
                        'kelas'       => $siswa['nama_kelas'] ?? '-',
                        'barcode_id'  => $siswa['barcode_id'],
                        'tanggal'     => $tanggalHariIni,
                        'jam'         => $absenAda['jam'],
                        'status'      => (int)$absenAda['status'],
                        'status_text' => $labelStatusLama,
                        'keterangan'  => 'Sudah Absen'
                    ]
                ]);
                exit;
            }
        } else {
            // Simpan status angka ke dalam kolom TINYINT absensi_siswa
            $stmtInsert = $pdo->prepare("
                INSERT INTO absensi_siswa (siswa_id, tanggal, jam, status)
                VALUES (:siswa_id, :tanggal, :jam, :status)
            ");
            $querySukses = $stmtInsert->execute([
                'siswa_id' => $siswa['id'],
                'tanggal'  => $tanggalHariIni,
                'jam'      => $jamSekarang,
                'status'   => $statusInput
            ]);
        }

        // Blok IF yang menangani query INSERT absensi siswa berhasil dieksekusi
        if ($querySukses) {
            // =========================================================================
            // KONVERSI ANGKA MENJADI TEKS & PENGIRIMAN NOTIFIKASI BOT TELEGRAM (cURL PHP)
            // =========================================================================
            $tokenBot = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : (getenv('TELEGRAM_BOT_TOKEN') ?: "");
            $chatId   = defined('TELEGRAM_CHAT_ID') ? TELEGRAM_CHAT_ID : (getenv('TELEGRAM_CHAT_ID') ?: "");

            $namaSiswa   = $siswa['nama'];
            $nisSiswa    = $siswa['nis'];
            $kelasSiswa  = $siswa['nama_kelas'] ?? '-';
            $waktuAbsen  = date('d-m-Y', strtotime($tanggalHariIni)) . ' pukul ' . substr($jamSekarang, 0, 5) . ' WIB';

            // Logika kondisional (switch-case) menerjemahkan angka status ke teks string & badge
            switch ((int)$statusInput) {
                case 1:
                    $statusString = "Sakit";
                    $badgeStatus  = "🤒 Sakit";
                    break;
                case 2:
                    $statusString = "Izin";
                    $badgeStatus  = "✉️ Izin";
                    break;
                case 3:
                    $statusString = "Alpha";
                    $badgeStatus  = "❌ Alpha / Tanpa Keterangan";
                    break;
                case 0:
                default:
                    $statusString = "Hadir";
                    $badgeStatus  = ($jamSekarang > '07:15:00') ? "⏰ Hadir (Terlambat)" : "✅ Hadir Tepat Waktu";
                    break;
            }

            // Format pesan string yang rapi menggunakan HTML
            $pesanTelegram  = "📢 <b>NOTIFIKASI PRESENSI SISWA - SMK NU WARGABINANGUN</b>\n";
            $pesanTelegram .= "━━━━━━━━━━━━━━━━━━━━\n";
            $pesanTelegram .= "👤 <b>Nama Siswa:</b> " . htmlspecialchars($namaSiswa) . "\n";
            $pesanTelegram .= "🆔 <b>NIS:</b> " . htmlspecialchars($nisSiswa) . "\n";
            $pesanTelegram .= "🏫 <b>Kelas:</b> " . htmlspecialchars($kelasSiswa) . "\n";
            $pesanTelegram .= "⏰ <b>Waktu:</b> " . $waktuAbsen . "\n";
            $pesanTelegram .= "📌 <b>Status Kehadiran:</b> " . $badgeStatus . "\n";
            $pesanTelegram .= "━━━━━━━━━━━━━━━━━━━━\n";
            $pesanTelegram .= "<i>Presensi tercatat otomatis via Scanner.</i>";

            // Kirim pesan via cURL PHP ke Telegram API
            $urlTelegram = "https://api.telegram.org/bot" . $tokenBot . "/sendMessage";
            $postData = [
                'chat_id'                  => $chatId,
                'text'                     => $pesanTelegram,
                'parse_mode'               => 'HTML',
                'disable_web_page_preview' => true
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $urlTelegram);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Wajib agar request HTTPS tidak ditolak di server lokal XAMPP
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $responseTelegram = curl_exec($ch);
            curl_close($ch);

            // =========================================================================
            // PENGIRIMAN NOTIFIKASI WHATSAPP KE ORANG TUA SISWA VIA FONNTE
            // =========================================================================
            $tokenFonnte = defined('FONNTE_TOKEN') ? FONNTE_TOKEN : (getenv('FONNTE_TOKEN') ?: "");
            $namaOrtu    = !empty($siswa['nama_ortu']) ? trim($siswa['nama_ortu']) : 'Bapak/Ibu Orang Tua / Wali';
            $targetWa    = !empty($siswa['no_hp_ortu']) ? $siswa['no_hp_ortu'] : (!empty($siswa['no_wa']) ? $siswa['no_wa'] : null);

            // Khusus: Jika siswa bernama Virandha Tiara Safana, fallback ke 0895391621414 bila nomor belum terisi
            if (empty($targetWa) && stripos($namaSiswa, 'Virandha') !== false) {
                $targetWa = "0895391621414";
            }

            if (!empty($targetWa)) {
                $pesanWa  = "📢 *NOTIFIKASI PRESENSI SISWA - SMK NU WARGABINANGUN*\n";
                $pesanWa .= "━━━━━━━━━━━━━━━━━━━━\n";
                $pesanWa .= "Kepada Yth: *" . $namaOrtu . "*\n\n";
                $pesanWa .= "Menginformasikan bahwa putra/putri Anda:\n";
                $pesanWa .= "👤 *Nama Siswa:* " . $namaSiswa . "\n";
                $pesanWa .= "🆔 *NIS:* " . $nisSiswa . "\n";
                $pesanWa .= "🏫 *Kelas:* " . $kelasSiswa . "\n";
                $pesanWa .= "⏰ *Waktu Absen:* " . $waktuAbsen . "\n";
                $pesanWa .= "📌 *Status Kehadiran:* " . $badgeStatus . "\n";
                $pesanWa .= "━━━━━━━━━━━━━━━━━━━━\n";
                $pesanWa .= "_Notifikasi otomatis dari Sistem Absensi SMK NU WARGABINANGUN._";

                $chWa = curl_init();
                curl_setopt_array($chWa, [
                    CURLOPT_URL            => "https://api.fonnte.com/send",
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => [
                        'target'      => $targetWa,
                        'message'     => $pesanWa,
                        'countryCode' => '62',
                    ],
                    CURLOPT_HTTPHEADER     => [
                        "Authorization: " . $tokenFonnte
                    ],
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_TIMEOUT        => 10,
                ]);
                $responseWa = curl_exec($chWa);
                curl_close($chWa);
            }
            // =========================================================================


            echo json_encode([
                'status'     => 'success',
                'keterangan' => 'Sudah Absen',
                'message'    => 'Presensi berhasil dicatat! Status: ' . $badgeStatus . '.',
                'data'       => [
                    'role'        => 'Siswa',
                    'nis'         => $siswa['nis'],
                    'nama'        => $siswa['nama'],
                    'kelas'       => $siswa['nama_kelas'] ?? '-',
                    'barcode_id'  => $siswa['barcode_id'],
                    'tanggal'     => $tanggalHariIni,
                    'jam'         => $jamSekarang,
                    'status'      => $statusInput,
                    'status_text' => $statusString,
                    'keterangan'  => 'Sudah Absen (' . $statusString . ')'
                ]
            ]);
            exit;
        } else {
            http_response_code(500);
            echo json_encode([
                'status'  => 'error',
                'message' => 'Gagal menyimpan data absensi siswa ke database.'
            ]);
            exit;
        }
    }

    // 6. Jika Bukan Siswa, Cari Data Guru Berdasarkan barcode_id ATAU nip
    $stmtGuru = $pdo->prepare("
        SELECT id, nip, nama, barcode_id, no_wa, no_hp
        FROM guru
        WHERE barcode_id = :code1 OR nip = :code2
        LIMIT 1
    ");
    $stmtGuru->execute(['code1' => $code, 'code2' => $code]);
    $guru = $stmtGuru->fetch();

    if ($guru) {
        // === PROSES PRESENSI GURU ===
        $stmtCekG = $pdo->prepare("
            SELECT id, jam_masuk, jam_pulang, status, tanggal
            FROM absensi_guru
            WHERE guru_id = :guru_id AND tanggal = :tanggal
            LIMIT 1
        ");
        $stmtCekG->execute([
            'guru_id' => $guru['id'],
            'tanggal' => $tanggalHariIni
        ]);
        $absenG = $stmtCekG->fetch();

        if (!$absenG || $absenG['status'] === 'alpa') {
            // Catat Masuk Guru (Belum ada record ATAU sebelumnya ditandai Alpha otomatis)
            $statusGuru = ($statusInput === 1) ? 'sakit' : (($statusInput === 2) ? 'izin' : (($statusInput === 3) ? 'alpa' : (($jamSekarang > '07:00:00') ? 'terlambat' : 'hadir')));

            if ($absenG && $absenG['status'] === 'alpa') {
                $stmtIn = $pdo->prepare("
                    UPDATE absensi_guru 
                    SET jam_masuk = :jam_masuk, status = :status 
                    WHERE id = :id
                ");
                $queryMasuk = $stmtIn->execute([
                    'jam_masuk' => $jamSekarang,
                    'status'    => $statusGuru,
                    'id'        => $absenG['id']
                ]);
            } else {
                $stmtIn = $pdo->prepare("
                    INSERT INTO absensi_guru (guru_id, tanggal, jam_masuk, status)
                    VALUES (:guru_id, :tanggal, :jam_masuk, :status)
                ");
                $queryMasuk = $stmtIn->execute([
                    'guru_id'   => $guru['id'],
                    'tanggal'   => $tanggalHariIni,
                    'jam_masuk' => $jamSekarang,
                    'status'    => $statusGuru
                ]);
            }

            if ($queryMasuk) {
                $tokenBot = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : (getenv('TELEGRAM_BOT_TOKEN') ?: "");
                $chatId   = defined('TELEGRAM_CHAT_ID') ? TELEGRAM_CHAT_ID : (getenv('TELEGRAM_CHAT_ID') ?: "");

                $namaGuru   = $guru['nama'];
                $nipGuru    = $guru['nip'];
                $waktuAbsen = date('d-m-Y', strtotime($tanggalHariIni)) . ' pukul ' . substr($jamSekarang, 0, 5) . ' WIB';

                switch ($statusGuru) {
                    case 'terlambat': $badgeGuru = "⏰ Masuk (Terlambat)"; break;
                    case 'sakit':     $badgeGuru = "🤒 Sakit"; break;
                    case 'izin':      $badgeGuru = "✉️ Izin"; break;
                    case 'alpa':      $badgeGuru = "❌ Alpha"; break;
                    default:          $badgeGuru = "✅ Masuk (Tepat Waktu)"; break;
                }

                $pesanTelegram  = "📢 <b>NOTIFIKASI PRESENSI GURU</b>\n";
                $pesanTelegram .= "━━━━━━━━━━━━━━━━━━━━\n";
                $pesanTelegram .= "👤 <b>Nama Guru:</b> " . htmlspecialchars($namaGuru) . "\n";
                $pesanTelegram .= "🆔 <b>NIP:</b> " . htmlspecialchars($nipGuru) . "\n";
                $pesanTelegram .= "⏰ <b>Waktu:</b> " . $waktuAbsen . "\n";
                $pesanTelegram .= "📌 <b>Status Kehadiran:</b> " . $badgeGuru . "\n";
                $pesanTelegram .= "━━━━━━━━━━━━━━━━━━━━\n";
                $pesanTelegram .= "<i>Presensi tercatat otomatis via Scanner.</i>";

                $urlTelegram = "https://api.telegram.org/bot" . $tokenBot . "/sendMessage";
                $postData = [
                    'chat_id'                  => $chatId,
                    'text'                     => $pesanTelegram,
                    'parse_mode'               => 'HTML',
                    'disable_web_page_preview' => true
                ];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $urlTelegram);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $responseTelegram = curl_exec($ch);
                curl_close($ch);

                // Kirim Notifikasi WhatsApp Guru Masuk
                $targetWaGuru = !empty($guru['no_wa']) ? $guru['no_wa'] : (!empty($guru['no_hp']) ? $guru['no_hp'] : null);
                if (!empty($targetWaGuru)) {
                    $tokenFonnte = defined('FONNTE_TOKEN') ? FONNTE_TOKEN : (getenv('FONNTE_TOKEN') ?: "");
                    $pesanWaGuru = "📢 *NOTIFIKASI PRESENSI GURU - SMK NU WARGABINANGUN*\n"
                                 . "━━━━━━━━━━━━━━━━━━━━\n"
                                 . "👤 *Nama Guru:* " . $namaGuru . "\n"
                                 . "🆔 *NIP:* " . $nipGuru . "\n"
                                 . "⏰ *Waktu:* " . $waktuAbsen . "\n"
                                 . "📌 *Status Kehadiran:* " . $badgeGuru . "\n"
                                 . "━━━━━━━━━━━━━━━━━━━━\n"
                                 . "_Presensi tercatat otomatis via Scanner Sistem Absensi._";

                    $chWaG = curl_init();
                    curl_setopt_array($chWaG, [
                        CURLOPT_URL            => "https://api.fonnte.com/send",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_POST           => true,
                        CURLOPT_POSTFIELDS     => [
                            'target'      => $targetWaGuru,
                            'message'     => $pesanWaGuru,
                            'countryCode' => '62',
                        ],
                        CURLOPT_HTTPHEADER     => [
                            "Authorization: " . $tokenFonnte
                        ],
                        CURLOPT_SSL_VERIFYPEER => false,
                        CURLOPT_TIMEOUT        => 10,
                    ]);
                    curl_exec($chWaG);
                    curl_close($chWaG);
                }


                echo json_encode([
                    'status'     => 'success',
                    'keterangan' => 'Sudah Absen',
                    'message'    => 'Presensi Guru berhasil dicatat! Status: ' . $badgeGuru . '.',
                    'data'       => [
                        'role'       => 'Guru',
                        'nip'        => $guru['nip'],
                        'nama'       => $guru['nama'],
                        'tanggal'    => $tanggalHariIni,
                        'jam'        => $jamSekarang,
                        'tipe'       => 'Masuk',
                        'status'     => $statusGuru,
                        'keterangan' => 'Sudah Absen'
                    ]
                ]);
                exit;
            }
        } else {
            // Jam Pulang Guru
            if ($absenG['jam_pulang'] !== null) {
                echo json_encode([
                    'status'     => 'warning',
                    'keterangan' => 'Sudah Absen',
                    'message'    => 'Keterangan: Guru sudah absen lengkap hari ini.',
                    'data'       => [
                        'role'       => 'Guru',
                        'nip'        => $guru['nip'],
                        'nama'       => $guru['nama'],
                        'tanggal'    => $tanggalHariIni,
                        'jam'        => $absenG['jam_masuk'],
                        'jam_masuk'  => $absenG['jam_masuk'],
                        'jam_pulang' => $absenG['jam_pulang'],
                        'tipe'       => 'Lengkap',
                        'status'     => $absenG['status'],
                        'keterangan' => 'Sudah Absen'
                    ]
                ]);
                exit;
            } else {
                $stmtOut = $pdo->prepare("UPDATE absensi_guru SET jam_pulang = :jam_pulang WHERE id = :id");
                $queryPulang = $stmtOut->execute([
                    'jam_pulang' => $jamSekarang,
                    'id'         => $absenG['id']
                ]);

                if ($queryPulang) {
                    $tokenBot = defined('TELEGRAM_BOT_TOKEN') ? TELEGRAM_BOT_TOKEN : (getenv('TELEGRAM_BOT_TOKEN') ?: "");
                    $chatId   = defined('TELEGRAM_CHAT_ID') ? TELEGRAM_CHAT_ID : (getenv('TELEGRAM_CHAT_ID') ?: "");

                    $waktuAbsen = date('d-m-Y', strtotime($tanggalHariIni)) . ' pukul ' . substr($jamSekarang, 0, 5) . ' WIB';
                    $pesanTelegram  = "📢 <b>NOTIFIKASI PRESENSI GURU</b>\n";
                    $pesanTelegram .= "━━━━━━━━━━━━━━━━━━━━\n";
                    $pesanTelegram .= "👤 <b>Nama Guru:</b> " . htmlspecialchars($guru['nama']) . "\n";
                    $pesanTelegram .= "🆔 <b>NIP:</b> " . htmlspecialchars($guru['nip']) . "\n";
                    $pesanTelegram .= "⏰ <b>Waktu:</b> " . $waktuAbsen . "\n";
                    $pesanTelegram .= "📌 <b>Status:</b> 🏠 Pulang\n";
                    $pesanTelegram .= "━━━━━━━━━━━━━━━━━━━━\n";
                    $pesanTelegram .= "<i>Presensi jam pulang berhasil dicatat.</i>";

                    $urlTelegram = "https://api.telegram.org/bot" . $tokenBot . "/sendMessage";
                    $postData = [
                        'chat_id'                  => $chatId,
                        'text'                     => $pesanTelegram,
                        'parse_mode'               => 'HTML',
                        'disable_web_page_preview' => true
                    ];

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $urlTelegram);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                    $responseTelegram = curl_exec($ch);
                    curl_close($ch);

                    // Kirim Notifikasi WhatsApp Guru Pulang
                    $targetWaGuru = !empty($guru['no_wa']) ? $guru['no_wa'] : (!empty($guru['no_hp']) ? $guru['no_hp'] : null);
                    if (!empty($targetWaGuru)) {
                        $tokenFonnte = defined('FONNTE_TOKEN') ? FONNTE_TOKEN : (getenv('FONNTE_TOKEN') ?: "");
                        $pesanWaGuru = "📢 *NOTIFIKASI PRESENSI GURU (PULANG) - SMK NU WARGABINANGUN*\n"
                                     . "━━━━━━━━━━━━━━━━━━━━\n"
                                     . "👤 *Nama Guru:* " . $guru['nama'] . "\n"
                                     . "🆔 *NIP:* " . $guru['nip'] . "\n"
                                     . "⏰ *Waktu:* " . $waktuAbsen . "\n"
                                     . "📌 *Status:* 🏠 Pulang\n"
                                     . "━━━━━━━━━━━━━━━━━━━━\n"
                                     . "_Presensi jam pulang berhasil dicatat via Scanner Sistem Absensi._";

                        $chWaG = curl_init();
                        curl_setopt_array($chWaG, [
                            CURLOPT_URL            => "https://api.fonnte.com/send",
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_POST           => true,
                            CURLOPT_POSTFIELDS     => [
                                'target'      => $targetWaGuru,
                                'message'     => $pesanWaGuru,
                                'countryCode' => '62',
                            ],
                            CURLOPT_HTTPHEADER     => [
                                "Authorization: " . $tokenFonnte
                            ],
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_TIMEOUT        => 10,
                        ]);
                        curl_exec($chWaG);
                        curl_close($chWaG);
                    }


                    echo json_encode([
                        'status'     => 'success',
                        'keterangan' => 'Sudah Absen',
                        'message'    => 'Jam Pulang berhasil dicatat! Keterangan: Sudah Absen Pulang.',
                        'data'       => [
                            'role'       => 'Guru',
                            'nip'        => $guru['nip'],
                            'nama'       => $guru['nama'],
                            'tanggal'    => $tanggalHariIni,
                            'jam'        => $jamSekarang,
                            'tipe'       => 'Pulang',
                            'status'     => 'pulang',
                            'keterangan' => 'Sudah Absen Pulang'
                        ]
                    ]);
                    exit;
                }
            }
        }
    }

    // 7. Jika tidak ditemukan di Siswa maupun Guru
    http_response_code(404);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Kode QR / Barcode / NIS / NIP "' . htmlspecialchars($code) . '" tidak ditemukan pada data siswa maupun guru.'
    ]);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status'  => 'error',
        'message' => 'Terjadi kesalahan sistem database: ' . $e->getMessage()
    ]);
    exit;
}
