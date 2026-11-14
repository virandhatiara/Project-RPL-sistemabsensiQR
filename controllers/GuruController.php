<?php
class GuruController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->checkAuth(['guru']);
    }

    public function dashboard() {
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date('Y-m-d');
        $hariIni = date('l'); // Monday, Tuesday, etc.

        // Mapping English to Indonesian
        $hariMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];
        $hariIndonesia = $hariMap[$hariIni] ?? $hariIni;

        $model = $this->model('GuruDashboardModel');

        $profil = $model->getProfilGuru((int)$_SESSION['user_id']);

        if (!$profil) {
            // Guru tidak memiliki data profil yang terhubung
            $data['judul'] = 'Dashboard Guru';
            $data['profil'] = null;
            $this->view('templates/header', $data);
            $this->view('guru/dashboard', $data);
            $this->view('templates/footer', $data);
            return;
        }

        $data['judul']          = 'Dashboard Guru';
        $data['profil']         = $profil;
        $data['absensi_hari_ini'] = $model->getAbsensiHariIni((int)$profil['id'], $tanggal);
        $data['jadwal_hari_ini']  = $model->getJadwalHariIni((int)$profil['id'], $hariIndonesia);
        $data['jadwal_semua']     = $model->getJadwalMingguIni((int)$profil['id']);
        $data['riwayat']          = $model->getRiwayatAbsensi((int)$profil['id'], 15);
        $data['rekap_bulan']      = $model->getRekapBulanIni((int)$profil['id']);
        $data['tanggal']          = $tanggal;
        $data['hari']             = $hariIndonesia;

        $this->view('templates/header', $data);
        $this->view('guru/dashboard', $data);
        $this->view('templates/footer', $data);
    }

    /**
     * Presensi Mandiri Langsung dari Dashboard Guru (Masuk / Pulang)
     */
    public function absenMandiri() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/guru/dashboard');
        }
        Csrf::validate();

        $model = $this->model('GuruDashboardModel');
        $profil = $model->getProfilGuru((int)$_SESSION['user_id']);
        if (!$profil) {
            Flasher::setFlash('Gagal!', 'Profil guru tidak ditemukan.', 'danger');
            $this->redirect('/guru/dashboard');
        }

        $absensiGuruModel = $this->model('AbsensiGuruModel');
        date_default_timezone_set('Asia/Jakarta');
        $tanggalHariIni = date('Y-m-d');
        $jamSekarang    = date('H:i:s');

        $absenHariIni = $absensiGuruModel->cekAbsensiHariIni($profil['id'], $tanggalHariIni);

        if (!$absenHariIni || $absenHariIni['status'] === 'alpa') {
            // Belum ada presensi ATAU sebelumnya otomatis ditandai Alpa -> Catat / Perbarui Jam Masuk
            $batasWaktu    = '07:00:00';
            $statusAbsensi = ($jamSekarang > $batasWaktu) ? 'terlambat' : 'hadir';

            if ($absenHariIni && $absenHariIni['status'] === 'alpa') {
                $berhasil = $absensiGuruModel->updateStatusMasuk($absenHariIni['id'], $jamSekarang, $statusAbsensi);
            } else {
                $dataAbsensi = [
                    'guru_id' => $profil['id'],
                    'tanggal' => $tanggalHariIni,
                    'jam'     => $jamSekarang,
                    'status'  => $statusAbsensi
                ];
                $berhasil = ($absensiGuruModel->catatJamMasuk($dataAbsensi) > 0);
            }

            if ($berhasil) {
                $statusMsg = ($statusAbsensi === 'terlambat') ? 'Terlambat' : 'Hadir Tepat Waktu';

                // Kirim notifikasi WhatsApp jika guru telah mengisi no HP/WA
                $targetWa = !empty($profil['no_wa']) ? $profil['no_wa'] : (!empty($profil['no_hp']) ? $profil['no_hp'] : null);
                if (!empty($targetWa)) {
                    $waktuAbsen = date('d-m-Y', strtotime($tanggalHariIni)) . ' pukul ' . substr($jamSekarang, 0, 5) . ' WIB';
                    $badgeGuru = ($statusAbsensi === 'terlambat') ? '⏰ Masuk (Terlambat)' : '✅ Masuk (Tepat Waktu)';
                    $pesanWaGuru = "📢 *NOTIFIKASI PRESENSI GURU (MASUK)*\n"
                                 . "━━━━━━━━━━━━━━━━━━━━\n"
                                 . "👤 *Nama Guru:* " . $profil['nama'] . "\n"
                                 . "🆔 *NIP:* " . $profil['nip'] . "\n"
                                 . "⏰ *Waktu:* " . $waktuAbsen . "\n"
                                 . "📌 *Status Kehadiran:* " . $badgeGuru . "\n"
                                 . "━━━━━━━━━━━━━━━━━━━━\n"
                                 . "_Presensi tercatat otomatis via Portal Guru SMK NU WARGABINANGUN._";
                    $this->kirimNotifikasiWhatsApp($targetWa, $pesanWaGuru);
                }

                Flasher::setFlash('Berhasil!', 'Jam Masuk berhasil dicatat pada pukul ' . substr($jamSekarang, 0, 5) . ' WIB (Status: ' . $statusMsg . ').', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Gagal mencatat jam masuk ke database.', 'danger');
            }
        } else {
            // Sudah ada jam masuk -> Cek Jam Pulang
            if ($absenHariIni['jam_pulang'] !== null) {
                Flasher::setFlash('Perhatian!', 'Presensi hari ini sudah lengkap (Masuk & Pulang).', 'warning');
            } else {
                if ($absensiGuruModel->catatJamPulang($absenHariIni['id'], $jamSekarang)) {
                    // Kirim notifikasi WhatsApp Pulang jika no HP terdaftar
                    $targetWa = !empty($profil['no_wa']) ? $profil['no_wa'] : (!empty($profil['no_hp']) ? $profil['no_hp'] : null);
                    if (!empty($targetWa)) {
                        $waktuAbsen = date('d-m-Y', strtotime($tanggalHariIni)) . ' pukul ' . substr($jamSekarang, 0, 5) . ' WIB';
                        $pesanWaGuru = "📢 *NOTIFIKASI PRESENSI GURU (PULANG)*\n"
                                     . "━━━━━━━━━━━━━━━━━━━━\n"
                                     . "👤 *Nama Guru:* " . $profil['nama'] . "\n"
                                     . "🆔 *NIP:* " . $profil['nip'] . "\n"
                                     . "⏰ *Waktu:* " . $waktuAbsen . "\n"
                                     . "📌 *Status:* 🏠 Pulang\n"
                                     . "━━━━━━━━━━━━━━━━━━━━\n"
                                     . "_Presensi jam pulang berhasil dicatat via Portal Guru SMK NU WARGABINANGUN._";
                        $this->kirimNotifikasiWhatsApp($targetWa, $pesanWaGuru);
                    }

                    Flasher::setFlash('Berhasil!', 'Jam Pulang berhasil dicatat pada pukul ' . substr($jamSekarang, 0, 5) . ' WIB.', 'success');
                } else {
                    Flasher::setFlash('Gagal!', 'Gagal mencatat jam pulang ke database.', 'danger');
                }
            }
        }

        $this->redirect('/guru/dashboard');
    }

    /**
     * Memperbarui / Menambahkan Nomor HP / WhatsApp Guru Sendiri
     */
    public function updateNoHp() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/guru/dashboard');
        }
        Csrf::validate();

        $model = $this->model('GuruDashboardModel');
        $profil = $model->getProfilGuru((int)$_SESSION['user_id']);
        if (!$profil) {
            Flasher::setFlash('Gagal!', 'Profil guru tidak ditemukan.', 'danger');
            $this->redirect('/guru/dashboard');
        }

        $rawNoHp = trim($_POST['no_hp'] ?? ($_POST['no_wa'] ?? ''));
        $cleanNoHp = preg_replace('/[^0-9]/', '', $rawNoHp);

        if (empty($cleanNoHp)) {
            Flasher::setFlash('Gagal!', 'Nomor HP / WhatsApp wajib diisi.', 'danger');
            $this->redirect('/guru/dashboard');
        }

        if (strlen($cleanNoHp) < 9 || strlen($cleanNoHp) > 16) {
            Flasher::setFlash('Gagal!', 'Format nomor WhatsApp tidak valid (minimal 9 digit dan maksimal 16 digit).', 'danger');
            $this->redirect('/guru/dashboard');
        }

        if ($model->updateNoHp((int)$profil['id'], $cleanNoHp)) {
            Flasher::setFlash('Berhasil!', 'Nomor HP / WhatsApp absensi berhasil disimpan: ' . $cleanNoHp, 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Gagal menyimpan nomor HP ke database.', 'danger');
        }

        $this->redirect('/guru/dashboard');
    }

    /**
     * Helper Pengiriman WhatsApp via Fonnte API
     */
    private function kirimNotifikasiWhatsApp($nomorTujuan, $pesan) {
        if (empty($nomorTujuan) || empty($pesan)) return false;
        $token = (defined('FONNTE_TOKEN') && !empty(FONNTE_TOKEN)) ? FONNTE_TOKEN : (getenv('FONNTE_TOKEN') ?: '');
        if (!function_exists('curl_init')) return false;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => [
                'target'      => $nomorTujuan,
                'message'     => $pesan,
                'countryCode' => '62'
            ],
            CURLOPT_HTTPHEADER     => [
                'Authorization: ' . $token
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 8
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}
