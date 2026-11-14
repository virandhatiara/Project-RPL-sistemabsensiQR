<?php
class SiswaController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->checkAuth(['siswa']);
    }

    public function dashboard() {
        $data['judul'] = 'Portal Absensi Siswa';
        
        $portalModel = $this->model('SiswaPortalModel');
        
        // 1. Dapatkan Data Diri Siswa dari Sesi Login
        $profil = $portalModel->getProfilSiswa($_SESSION['user_id']);
        
        // Jika karena alasan tertentu data siswanya tidak ketemu walau user_id nya ada
        if (!$profil) {
            $this->view('templates/header', ['judul' => 'Error']);
            echo '<div class="container py-5"><div class="alert alert-danger">Profil Siswa tidak ditemukan pada sistem.</div></div>';
            $this->view('templates/footer');
            exit;
        }

        $data['profil'] = $profil;

        // 2. Dapatkan Rekapitulasi Hadir/Sakit (Total Keseluruhan)
        $data['rekap'] = $portalModel->getRekapAbsensi($profil['id']);

        // 3. Tangkap Parameter Filter dari URL (GET)
        $filters = [
            'tgl_awal'  => isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : '',
            'tgl_akhir' => isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '',
            'bulan'     => isset($_GET['bulan']) ? $_GET['bulan'] : date('Y-m'), // Default bulan ini jika kosong
            'status'    => isset($_GET['status']) ? $_GET['status'] : ''
        ];
        
        // Jika rentang tanggal diisi, abaikan filter bulan
        if (!empty($filters['tgl_awal']) && !empty($filters['tgl_akhir'])) {
            $filters['bulan'] = '';
        }

        $data['filters'] = $filters;

        // 4. Dapatkan Riwayat Absensi Berdasarkan Filter (SANGAT AMAN: diikat ke $profil['id'])
        $data['riwayat'] = $portalModel->getRiwayatFilter($profil['id'], $filters);

        // 5. Dapatkan Jadwal Belajar Kelas Siswa (Hari Ini & Seminggu Penuh)
        date_default_timezone_set('Asia/Jakarta');
        $hariMap = [
            'Monday'    => 'Senin',
            'Tuesday'   => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday'  => 'Kamis',
            'Friday'    => 'Jumat',
            'Saturday'  => 'Sabtu',
            'Sunday'    => 'Minggu',
        ];
        $hariIndonesia = $hariMap[date('l')] ?? date('l');

        $data['hari']             = $hariIndonesia;
        $data['tanggal']          = date('Y-m-d');
        $data['jadwal_hari_ini']  = !empty($profil['kelas_id']) ? $portalModel->getJadwalHariIni((int)$profil['kelas_id'], $hariIndonesia) : [];
        $data['jadwal_semua']     = !empty($profil['kelas_id']) ? $portalModel->getJadwalSeminggu((int)$profil['kelas_id']) : [];

        // Load Views
        $this->view('templates/header', $data);
        $this->view('siswa/dashboard', $data);
        $this->view('templates/footer', $data);
    }

    /**
     * Presensi Mandiri Langsung dari Dashboard Siswa
     */
    public function absenMandiri() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/siswa/dashboard');
        }
        Csrf::validate();

        $portalModel = $this->model('SiswaPortalModel');
        $profil = $portalModel->getProfilSiswa($_SESSION['user_id']);
        if (!$profil) {
            Flasher::setFlash('Gagal!', 'Profil siswa tidak ditemukan.', 'danger');
            $this->redirect('/siswa/dashboard');
        }

        $absensiModel = $this->model('AbsensiSiswaModel');
        date_default_timezone_set('Asia/Jakarta');
        $tanggalHariIni = date('Y-m-d');
        $jamSekarang    = date('H:i:s');

        // Cek apakah sudah absen hari ini
        $sudahAbsen = $absensiModel->cekAbsensiHariIni($profil['id'], $tanggalHariIni);
        $isUpdateFromAlpha = false;

        if ($sudahAbsen) {
            if ((int)$sudahAbsen['status'] === 3) {
                // Sebelumnya ditandai Alpha otomatis karena belum absen, perbolehkan presensi Hadir
                $isUpdateFromAlpha = true;
            } else {
                Flasher::setFlash('Perhatian!', 'Anda sudah melakukan presensi hari ini pada pukul ' . substr($sudahAbsen['jam'], 0, 5) . ' WIB.', 'warning');
                $this->redirect('/siswa/dashboard');
            }
        }

        // Status kehadiran mandiri siswa: 0 = Hadir
        $statusAbsensi = 0;

        $dataAbsensi = [
            'siswa_id' => $profil['id'],
            'tanggal'  => $tanggalHariIni,
            'jam'      => $jamSekarang,
            'status'   => $statusAbsensi
        ];

        $sukses = false;
        if ($isUpdateFromAlpha && isset($sudahAbsen['id'])) {
            $sukses = $absensiModel->updateAbsensi($sudahAbsen['id'], $dataAbsensi);
        } else {
            $sukses = ($absensiModel->tambahAbsensi($dataAbsensi) > 0);
        }

        if ($sukses) {
            $pesan = 'Presensi berhasil dicatat (Status: Hadir pada ' . substr($jamSekarang, 0, 5) . ' WIB).';
            Flasher::setFlash('Berhasil!', $pesan, 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Gagal menyimpan data presensi ke database.', 'danger');
        }

        $this->redirect('/siswa/dashboard');
    }
}
