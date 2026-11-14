<?php
class AbsensiController extends Controller {
    public function __construct() {
        // We override the parent constructor to customize CSRF check.
        // API endpoints (e.g. from Arduino/Hardware) do not use CSRF token.
        
        $url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : '';
        $urlParams = explode('/', $url);
        $method = isset($urlParams[1]) ? $urlParams[1] : '';

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            // Exclude api_scan from CSRF validation
            if ($method !== 'api_scan') {
                Csrf::validate();
            }
        }
    }

    public function siswa() {
        Csrf::getToken(); // Ensure CSRF token is initialized in session
        $data['judul'] = 'Scan Absensi Siswa';
        
        // Cek jika ada flash data hasil scan
        $data['scan_result'] = isset($_SESSION['scan_result']) ? $_SESSION['scan_result'] : null;
        unset($_SESSION['scan_result']);

        $this->view('templates/header', $data);
        $this->view('absensi/siswa', $data);
        $this->view('templates/footer', $data);
    }

    public function scanSiswa() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            
            $rawJson = null;
            // Baca kode dari POST atau raw JSON
            $barcode_id = isset($_POST['barcode_id']) ? trim($_POST['barcode_id']) : '';
            if (empty($barcode_id) && isset($_POST['nis'])) {
                $barcode_id = trim($_POST['nis']);
            }
            if (empty($barcode_id)) {
                $rawJson = json_decode(file_get_contents('php://input'), true);
                if (is_array($rawJson)) {
                    $barcode_id = trim($rawJson['barcode_id'] ?? $rawJson['nis'] ?? '');
                }
            }

            // Baca status (0 = Hadir, 1 = Sakit, 2 = Izin, 3 = Alpha)
            $status = isset($_POST['status']) ? trim($_POST['status']) : '';
            if ($status === '' && is_array($rawJson) && isset($rawJson['status'])) {
                $status = trim((string)$rawJson['status']);
            }
            if ($status === '') {
                $status = '0';
            }

            $result = $this->processSiswaScan($barcode_id, $status);

            // Cek jika dipanggil via AJAX/Fetch
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                   || (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1');

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($result);
                exit;
            }

            $_SESSION['scan_result'] = $result;
            $this->redirect('/absensi/siswa');
        } else {
            $this->redirect('/absensi/siswa');
        }
    }

    // Endpoint API internal untuk Hardware Barcode Scanner
    public function api_scan() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Bisa menerima form-data, x-www-form-urlencoded, atau JSON body
            $barcode_id = isset($_POST['barcode_id']) ? $_POST['barcode_id'] : null;
            $status     = isset($_POST['status']) ? $_POST['status'] : 'hadir';
            
            if (!$barcode_id) {
                // Coba parse raw JSON body
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
                $barcode_id = isset($data['barcode_id']) ? $data['barcode_id'] : ($data['nis'] ?? null);
                if (isset($data['status'])) {
                    $status = $data['status'];
                }
            }

            if (!$barcode_id) {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Barcode ID atau NIS tidak ditemukan dalam request.']);
                exit;
            }

            $result = $this->processSiswaScan(trim($barcode_id), $status);

            if ($result['status'] == 'success') {
                http_response_code(200);
            } else {
                http_response_code(400);
            }
            
            echo json_encode($result);
            exit;
        } else {
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
            exit;
        }
    }

    private function processSiswaScan($barcode_id, $status = 'hadir') {
        if (empty($barcode_id)) {
            return [
                'status' => 'error',
                'message' => 'Kode Barcode / QR / NIS kosong.'
            ];
        }

        $siswaModel = $this->model('SiswaModel');
        // Cari berdasarkan barcode_id terlebih dahulu
        $siswa = $siswaModel->getSiswaByBarcode($barcode_id);

        // Jika tidak ditemukan, coba cari berdasarkan NIS
        if (!$siswa) {
            $siswa = $siswaModel->getSiswaByNis($barcode_id);
        }

        // Jika tidak ditemukan di siswa, cek apakah ini kode milik Guru (Universal Scanner)
        if (!$siswa) {
            $guruModel = $this->model('GuruModel');
            $guru = $guruModel->getGuruByBarcode($barcode_id);
            if (!$guru) {
                $guru = $guruModel->getGuruByNip($barcode_id);
            }
            if ($guru) {
                return $this->processGuruScan($barcode_id, false);
            }

            return [
                'status' => 'error',
                'message' => 'Kode QR / Barcode "' . htmlspecialchars($barcode_id) . '" belum terdaftar pada siswa maupun guru.'
            ];
        }

        $kelasModel = $this->model('KelasModel');
        $kelas = $kelasModel->getKelasById($siswa['kelas_id']);
        $nama_kelas = $kelas ? $kelas['nama_kelas'] : '-';

        $absensiModel = $this->model('AbsensiSiswaModel');
        date_default_timezone_set('Asia/Jakarta');
        $tanggalHariIni = date('Y-m-d');
        $jamSekarang = date('H:i:s');

        // Cek apakah sudah absen hari ini
        $sudahAbsen = $absensiModel->cekAbsensiHariIni($siswa['id'], $tanggalHariIni);

        $isUpdateAlpha = false;
        if ($sudahAbsen) {
            $statusLama = (int)$sudahAbsen['status'];
            if ($statusLama === 3) {
                // Sebelumnya ditandai Alpha otomatis karena belum absen, perbolehkan update ke Hadir/Sakit/Izin
                $isUpdateAlpha = true;
            } else {
                switch ($statusLama) {
                    case 1:  $labelLama = 'Sakit'; break;
                    case 2:  $labelLama = 'Izin';  break;
                    case 0:
                    default: $labelLama = 'Hadir'; break;
                }

                return [
                    'status'     => 'warning',
                    'keterangan' => 'Sudah Absen',
                    'message'    => 'Keterangan: Siswa sudah melakukan absensi hari ini dengan status "' . $labelLama . '" pada pukul ' . substr($sudahAbsen['jam'], 0, 5) . ' WIB.',
                    'data'       => [
                        'role'       => 'Siswa',
                        'nis'        => $siswa['nis'],
                        'nama'       => $siswa['nama'],
                        'kelas'      => $nama_kelas,
                        'tanggal'    => $tanggalHariIni,
                        'jam'        => $sudahAbsen['jam'],
                        'status'     => (int)$sudahAbsen['status'],
                        'keterangan' => 'Sudah Absen'
                    ]
                ];
            }
        }

        // Konversi status ke integer: 0 = Hadir, 1 = Sakit, 2 = Izin, 3 = Alpha
        if (is_numeric($status)) {
            $statusInt = (int)$status;
        } else {
            $statusMap = ['hadir' => 0, 'sakit' => 1, 'izin' => 2, 'alpa' => 3, 'alpha' => 3, 'terlambat' => 0];
            $statusInt = $statusMap[strtolower(trim($status))] ?? 0;
        }
        if ($statusInt < 0 || $statusInt > 3) {
            $statusInt = 0;
        }

        // Simpan absensi sebagai integer
        $dataAbsensi = [
            'siswa_id' => $siswa['id'],
            'tanggal'  => $tanggalHariIni,
            'jam'      => $jamSekarang,
            'status'   => $statusInt
        ];

        $suksesSimpan = false;
        if ($isUpdateAlpha && isset($sudahAbsen['id'])) {
            $suksesSimpan = $absensiModel->updateAbsensi($sudahAbsen['id'], $dataAbsensi);
        } else {
            $suksesSimpan = ($absensiModel->tambahAbsensi($dataAbsensi) > 0);
        }

        if ($suksesSimpan) {
            // Logika kondisional (switch-case) menerjemahkan angka integer menjadi teks string
            switch ($statusInt) {
                case 1:
                    $badgeStatus = "🤒 Sakit";
                    $statusLabel = "Sakit";
                    break;
                case 2:
                    $badgeStatus = "✉️ Izin";
                    $statusLabel = "Izin";
                    break;
                case 3:
                    $badgeStatus = "❌ Alpha";
                    $statusLabel = "Alpha";
                    break;
                case 0:
                default:
                    $badgeStatus = "✅ Hadir";
                    $statusLabel = "Hadir";
                    break;
            }

            // Kirim Notifikasi Telegram
            $waktuAbsenFmt = date('d-m-Y', strtotime($tanggalHariIni)) . ' pukul ' . substr($jamSekarang, 0, 5) . ' WIB';
            $pesanTelegram = "📢 <b>NOTIFIKASI PRESENSI SISWA - SMK NU WARGABINANGUN</b>\n"
                           . "━━━━━━━━━━━━━━━━━━━━\n"
                           . "👤 <b>Nama Siswa:</b> " . htmlspecialchars($siswa['nama']) . "\n"
                           . "🆔 <b>NIS:</b> " . htmlspecialchars($siswa['nis']) . "\n"
                           . "🏫 <b>Kelas:</b> " . htmlspecialchars($nama_kelas) . "\n"
                           . "⏰ <b>Waktu Absen:</b> " . $waktuAbsenFmt . "\n"
                           . "📌 <b>Status Kehadiran:</b> " . $badgeStatus . "\n"
                           . "━━━━━━━━━━━━━━━━━━━━\n"
                           . "<i>Presensi tercatat via Scanner Absensi.</i>";
            $this->kirimNotifikasiTelegram($pesanTelegram);

            // Kirim Notifikasi WhatsApp Fonnte ke Orang Tua
            $namaOrtu = !empty($siswa['nama_ortu']) ? trim($siswa['nama_ortu']) : 'Bapak/Ibu Orang Tua / Wali';
            $targetWa = !empty($siswa['no_hp_ortu']) ? $siswa['no_hp_ortu'] : (!empty($siswa['no_wa']) ? $siswa['no_wa'] : null);
            if (empty($targetWa) && stripos($siswa['nama'], 'Virandha') !== false) {
                $targetWa = '0895391621414';
            }
            if (!empty($targetWa)) {
                $pesanWa = "📢 *NOTIFIKASI PRESENSI SISWA - SMK NU WARGABINANGUN*\n"
                         . "━━━━━━━━━━━━━━━━━━━━\n"
                         . "Kepada Yth: *" . $namaOrtu . "*\n\n"
                         . "Menginformasikan bahwa putra/putri Anda:\n"
                         . "👤 *Nama Siswa:* " . $siswa['nama'] . "\n"
                         . "🆔 *NIS:* " . $siswa['nis'] . "\n"
                         . "🏫 *Kelas:* " . $nama_kelas . "\n"
                         . "⏰ *Waktu Absen:* " . $waktuAbsenFmt . "\n"
                         . "📌 *Status Kehadiran:* " . $badgeStatus . "\n"
                         . "━━━━━━━━━━━━━━━━━━━━\n"
                         . "_Notifikasi otomatis dari Sistem Absensi SMK NU WARGABINANGUN._";
                $this->kirimNotifikasiWhatsApp($targetWa, $pesanWa);
            }


            return [
                'status'     => 'success',
                'keterangan' => 'Sudah Absen',
                'message'    => 'Presensi berhasil dicatat! Status: ' . $badgeStatus . '.',
                'data'       => [
                    'role'       => 'Siswa',
                    'nis'        => $siswa['nis'],
                    'nama'       => $siswa['nama'],
                    'kelas'      => $nama_kelas,
                    'tanggal'    => $tanggalHariIni,
                    'jam'        => $jamSekarang,
                    'status'     => $statusAbsensi,
                    'keterangan' => 'Sudah Absen'
                ]
            ];
        } else {
            return [
                'status' => 'error',
                'message' => 'Gagal menyimpan data absensi ke database.'
            ];
        }
    }

    public function guru() {
        Csrf::getToken(); // Ensure CSRF token is initialized in session
        $data['judul'] = 'Scan Absensi Guru';
        
        $data['scan_result'] = isset($_SESSION['scan_result']) ? $_SESSION['scan_result'] : null;
        unset($_SESSION['scan_result']);

        $this->view('templates/header', $data);
        $this->view('absensi/guru', $data);
        $this->view('templates/footer', $data);
    }

    public function scanGuru() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();

            // Baca kode dari POST atau raw JSON
            $barcode_id = isset($_POST['barcode_id']) ? trim($_POST['barcode_id']) : '';
            if (empty($barcode_id) && isset($_POST['nip'])) {
                $barcode_id = trim($_POST['nip']);
            }
            if (empty($barcode_id)) {
                $rawJson = json_decode(file_get_contents('php://input'), true);
                if (is_array($rawJson)) {
                    $barcode_id = trim($rawJson['barcode_id'] ?? $rawJson['nip'] ?? '');
                }
            }

            $result = $this->processGuruScan($barcode_id);

            // Jika dipanggil via AJAX/Fetch
            $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                   || (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1');

            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($result);
                exit;
            }

            // Jika dipanggil via Form Submit Biasa
            $_SESSION['scan_result'] = $result;
            $this->redirect('/absensi/guru');
        } else {
            $this->redirect('/absensi/guru');
        }
    }

    private function processGuruScan($barcode_id, $checkFallback = true) {
        if (empty($barcode_id)) {
            return ['status' => 'error', 'message' => 'Kode Barcode / QR / NIP kosong.'];
        }

        $guruModel = $this->model('GuruModel');
        $guru = $guruModel->getGuruByBarcode($barcode_id);

        if (!$guru) {
            $guru = $guruModel->getGuruByNip($barcode_id);
        }

        // Jika tidak ditemukan di guru dan checkFallback aktif, cek apakah ini Siswa!
        if (!$guru && $checkFallback) {
            $siswaModel = $this->model('SiswaModel');
            $siswa = $siswaModel->getSiswaByBarcode($barcode_id);
            if (!$siswa) {
                $siswa = $siswaModel->getSiswaByNis($barcode_id);
            }
            if ($siswa) {
                return $this->processSiswaScan($barcode_id);
            }

            return ['status' => 'error', 'message' => 'Kode QR / Barcode "' . htmlspecialchars($barcode_id) . '" belum terdaftar pada guru maupun siswa.'];
        }

        if (!$guru) {
            return ['status' => 'error', 'message' => 'Data Guru dengan kode/NIP "' . htmlspecialchars($barcode_id) . '" belum terdaftar.'];
        }

        $absensiGuruModel = $this->model('AbsensiGuruModel');
        
        date_default_timezone_set('Asia/Jakarta');
        $tanggalHariIni = date('Y-m-d');
        $jamSekarang = date('H:i:s');

        // Cek absensi hari ini
        $absenHariIni = $absensiGuruModel->cekAbsensiHariIni($guru['id'], $tanggalHariIni);

        if (!$absenHariIni || $absenHariIni['status'] === 'alpa') {
            // Belum ada record sama sekali hari ini ATAU sebelumnya ditandai Alpha otomatis -> SCAN MASUK
            $batasWaktu = '07:00:00';
            $statusAbsensi = ($jamSekarang > $batasWaktu) ? 'terlambat' : 'hadir';
            
            if ($absenHariIni && $absenHariIni['status'] === 'alpa') {
                $berhasilMasuk = $absensiGuruModel->updateStatusMasuk($absenHariIni['id'], $jamSekarang, $statusAbsensi);
            } else {
                $dataAbsensi = [
                    'guru_id' => $guru['id'],
                    'tanggal' => $tanggalHariIni,
                    'jam'     => $jamSekarang,
                    'status'  => $statusAbsensi
                ];
                $berhasilMasuk = ($absensiGuruModel->catatJamMasuk($dataAbsensi) > 0);
            }

            if ($berhasilMasuk) {
                $statusMsg = ($statusAbsensi === 'terlambat') ? 'Terlambat' : 'Tepat Waktu';

                // Kirim Notifikasi Telegram Masuk
                $waktuAbsenFmt  = date('d-m-Y', strtotime($tanggalHariIni)) . ' pukul ' . substr($jamSekarang, 0, 5) . ' WIB';
                $pesanTelegramG = "📢 <b>NOTIFIKASI PRESENSI GURU (MASUK)</b>\n"
                                . "━━━━━━━━━━━━━━━━━━━━\n"
                                . "👤 <b>Nama Guru:</b> " . htmlspecialchars($guru['nama']) . "\n"
                                . "🆔 <b>NIP:</b> " . htmlspecialchars($guru['nip']) . "\n"
                                . "⏰ <b>Waktu Absen:</b> " . $waktuAbsenFmt . "\n"
                                . "📌 <b>Keterangan:</b> Masuk (" . $statusMsg . ")\n"
                                . "━━━━━━━━━━━━━━━━━━━━\n"
                                . "<i>Presensi jam masuk tercatat via Scanner.</i>";
                $this->kirimNotifikasiTelegram($pesanTelegramG);

                // Kirim Notifikasi WhatsApp Guru Masuk
                $targetWaGuru = !empty($guru['no_wa']) ? $guru['no_wa'] : (!empty($guru['no_hp']) ? $guru['no_hp'] : null);
                if (!empty($targetWaGuru)) {
                    $pesanWaG = "📢 *NOTIFIKASI PRESENSI GURU - SMK NU WARGABINANGUN*\n"
                              . "━━━━━━━━━━━━━━━━━━━━\n"
                              . "👤 *Nama Guru:* " . $guru['nama'] . "\n"
                              . "🆔 *NIP:* " . $guru['nip'] . "\n"
                              . "⏰ *Waktu Absen:* " . $waktuAbsenFmt . "\n"
                              . "📌 *Keterangan:* Masuk (" . $statusMsg . ")\n"
                              . "━━━━━━━━━━━━━━━━━━━━\n"
                              . "_Presensi jam masuk tercatat via Scanner Sistem Absensi._";
                    $this->kirimNotifikasiWhatsApp($targetWaGuru, $pesanWaG);
                }

                return [
                    'status'     => 'success',
                    'keterangan' => 'Sudah Absen',
                    'message'    => 'Jam Masuk Berhasil Dicatat! Keterangan: Sudah Absen Masuk (' . $statusMsg . ').',
                    'data'       => [
                        'role'       => 'Guru',
                        'nip'        => $guru['nip'],
                        'nama'       => $guru['nama'],
                        'tanggal'    => $tanggalHariIni,
                        'jam'        => $jamSekarang,
                        'tipe'       => 'Masuk',
                        'status'     => $statusAbsensi,
                        'keterangan' => 'Sudah Absen'
                    ]
                ];
            } else {
                return ['status' => 'error', 'message' => 'Gagal mencatat jam masuk.'];
            }
        } else {
            // Sudah ada record hari ini
            if ($absenHariIni['jam_pulang'] !== null) {
                // Sudah ada jam pulang -> ABSEN LENGKAP
                return [
                    'status'     => 'warning',
                    'keterangan' => 'Sudah Absen',
                    'message'    => 'Keterangan: Sudah Absen lengkap hari ini (Masuk: ' . substr($absenHariIni['jam_masuk'], 0, 5) . ', Pulang: ' . substr($absenHariIni['jam_pulang'], 0, 5) . ' WIB).',
                    'data'       => [
                        'role'       => 'Guru',
                        'nip'        => $guru['nip'],
                        'nama'       => $guru['nama'],
                        'tanggal'    => $tanggalHariIni,
                        'jam'        => $absenHariIni['jam_masuk'],
                        'jam_masuk'  => $absenHariIni['jam_masuk'],
                        'jam_pulang' => $absenHariIni['jam_pulang'],
                        'tipe'       => 'Lengkap',
                        'status'     => 'hadir',
                        'keterangan' => 'Sudah Absen'
                    ]
                ];
            } else {
                // Belum ada jam pulang -> SCAN PULANG
                $jamMasuk = strtotime($tanggalHariIni . ' ' . $absenHariIni['jam_masuk']);
                $sekarang = strtotime($tanggalHariIni . ' ' . $jamSekarang);
                $selisihMenit = round(abs($sekarang - $jamMasuk) / 60, 2);

                if ($selisihMenit < 10) {
                    return [
                        'status'     => 'warning',
                        'keterangan' => 'Sudah Absen',
                        'message'    => 'Keterangan: Guru sudah absen Masuk pada pukul ' . substr($absenHariIni['jam_masuk'], 0, 5) . ' WIB. Jeda waktu kepulangan terlalu dekat (' . round($selisihMenit) . ' menit).',
                        'data'       => [
                            'role'       => 'Guru',
                            'nip'        => $guru['nip'],
                            'nama'       => $guru['nama'],
                            'tanggal'    => $tanggalHariIni,
                            'jam'        => $absenHariIni['jam_masuk'],
                            'tipe'       => 'Masuk',
                            'keterangan' => 'Sudah Absen'
                        ]
                    ];
                }

                if ($absensiGuruModel->catatJamPulang($absenHariIni['id'], $jamSekarang)) {
                    // Kirim Notifikasi Telegram Pulang
                    $waktuAbsenFmt  = date('d-m-Y', strtotime($tanggalHariIni)) . ' pukul ' . substr($jamSekarang, 0, 5) . ' WIB';
                    $pesanTelegramP = "📢 <b>NOTIFIKASI PRESENSI GURU (PULANG)</b>\n"
                                    . "━━━━━━━━━━━━━━━━━━━━\n"
                                    . "👤 <b>Nama Guru:</b> " . htmlspecialchars($guru['nama']) . "\n"
                                    . "🆔 <b>NIP:</b> " . htmlspecialchars($guru['nip']) . "\n"
                                    . "⏰ <b>Waktu Absen:</b> " . $waktuAbsenFmt . "\n"
                                    . "📌 <b>Keterangan:</b> Pulang\n"
                                    . "━━━━━━━━━━━━━━━━━━━━\n"
                                    . "<i>Presensi jam pulang tercatat via Scanner.</i>";
                    $this->kirimNotifikasiTelegram($pesanTelegramP);

                    // Kirim Notifikasi WhatsApp Guru Pulang
                    $targetWaGuru = !empty($guru['no_wa']) ? $guru['no_wa'] : (!empty($guru['no_hp']) ? $guru['no_hp'] : null);
                    if (!empty($targetWaGuru)) {
                        $pesanWaP = "📢 *NOTIFIKASI PRESENSI GURU (PULANG) - SMK NU WARGABINANGUN*\n"
                                  . "━━━━━━━━━━━━━━━━━━━━\n"
                                  . "👤 *Nama Guru:* " . $guru['nama'] . "\n"
                                  . "🆔 *NIP:* " . $guru['nip'] . "\n"
                                  . "⏰ *Waktu Absen:* " . $waktuAbsenFmt . "\n"
                                  . "📌 *Keterangan:* Pulang\n"
                                  . "━━━━━━━━━━━━━━━━━━━━\n"
                                  . "_Presensi jam pulang tercatat via Scanner Sistem Absensi._";
                        $this->kirimNotifikasiWhatsApp($targetWaGuru, $pesanWaP);
                    }

                    return [
                        'status'     => 'success',
                        'keterangan' => 'Sudah Absen',
                        'message'    => 'Jam Pulang Berhasil Dicatat! Keterangan: Sudah Absen Pulang pada ' . substr($jamSekarang, 0, 5) . ' WIB.',
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
                    ];
                } else {
                    return ['status' => 'error', 'message' => 'Gagal mencatat jam pulang.'];
                }
            }
        }
    }

    /**
     * Helper kirim notifikasi ke Telegram Bot
     */
    private function kirimNotifikasiTelegram($pesan) {
        $botToken = (defined('TELEGRAM_BOT_TOKEN') && !empty(TELEGRAM_BOT_TOKEN)) ? TELEGRAM_BOT_TOKEN : (getenv('TELEGRAM_BOT_TOKEN') ?: '');
        $chatId   = (defined('TELEGRAM_CHAT_ID') && !empty(TELEGRAM_CHAT_ID)) ? TELEGRAM_CHAT_ID : (getenv('TELEGRAM_CHAT_ID') ?: '');

        if (empty($botToken) || empty($chatId)) {
            return false;
        }

        $url = "https://api.telegram.org/bot" . $botToken . "/sendMessage";
        $postData = [
            'chat_id'                  => $chatId,
            'text'                     => $pesan,
            'parse_mode'               => 'HTML',
            'disable_web_page_preview' => true
        ];

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } else {
            $options = [
                'http' => [
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($postData),
                    'timeout' => 5
                ]
            ];
            $context = stream_context_create($options);
            return @file_get_contents($url, false, $context);
        }
    }

    private function kirimNotifikasiWhatsApp($nomorTujuan, $pesan) {
        if (empty($nomorTujuan) || empty($pesan)) {
            return false;
        }

        $token = (defined('FONNTE_TOKEN') && !empty(FONNTE_TOKEN)) ? FONNTE_TOKEN : (getenv('FONNTE_TOKEN') ?: '');
        $postData = [
            'target'      => $nomorTujuan,
            'message'     => $pesan,
            'countryCode' => '62'
        ];

        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $postData,
                CURLOPT_HTTPHEADER     => [
                    'Authorization: ' . $token
                ],
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_TIMEOUT        => 8
            ]);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response;
        } else {
            $options = [
                'http' => [
                    'header'  => "Content-Type: application/x-www-form-urlencoded\r\nAuthorization: " . $token . "\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($postData),
                    'timeout' => 8
                ]
            ];
            $context = stream_context_create($options);
            return @file_get_contents('https://api.fonnte.com/send', false, $context);
        }
    }
}


