<?php
class WaliKelasController extends Controller {
    private $kelasBinaan;

    public function __construct() {
        parent::__construct();
        $this->checkAuth(['wali_kelas']);
        
        // Fetch kelas binaan
        $waliKelasModel = $this->model('WaliKelasModel');
        $this->kelasBinaan = $waliKelasModel->getKelasBinaan($_SESSION['user_id']);
    }

    private function checkHasKelas() {
        if (!$this->kelasBinaan) {
            $data['judul'] = 'Akses Ditolak';
            $this->view('templates/header', $data);
            echo '<div class="container py-5"><div class="alert alert-danger shadow">
                    <h4 class="alert-heading">Akses Ditolak!</h4>
                    <p>Anda belum ditugaskan sebagai Wali Kelas untuk kelas manapun. Silakan hubungi Administrator.</p>
                  </div></div>';
            $this->view('templates/footer', $data);
            exit;
        }
    }

    public function dashboard() {
        $this->checkHasKelas();
        $data['judul'] = 'Dashboard Wali Kelas';
        $data['kelas'] = $this->kelasBinaan;
        
        date_default_timezone_set('Asia/Jakarta');
        $tanggalHariIni = date('Y-m-d');
        
        $waliKelasModel = $this->model('WaliKelasModel');
        // Otomatis masukkan siswa yang belum absen sebagai Alpha
        $this->model('AbsensiSiswaModel')->autoDataAlphaSiswa($tanggalHariIni);

        // Ambil daftar siswa beserta status absensinya hari ini
        $data['siswa_hari_ini'] = $waliKelasModel->getSiswaWithAbsensiHariIni($this->kelasBinaan['id'], $tanggalHariIni);
        
        // Kalkulasi ringkasan hari ini
        $data['ringkasan'] = [
            'total' => count($data['siswa_hari_ini']),
            'hadir' => 0,
            'sakit' => 0,
            'izin' => 0,
            'alpa' => 0,
            'belum_absen' => 0
        ];

        foreach ($data['siswa_hari_ini'] as $s) {
            if ($s['status'] === null || $s['status'] === '') {
                $data['ringkasan']['belum_absen']++;
            } else {
                $st = (int)$s['status'];
                if ($st === 0 || $s['status'] == 'hadir' || $s['status'] == 'terlambat') $data['ringkasan']['hadir']++;
                elseif ($st === 1 || $s['status'] == 'sakit') $data['ringkasan']['sakit']++;
                elseif ($st === 2 || $s['status'] == 'izin') $data['ringkasan']['izin']++;
                elseif ($st === 3 || $s['status'] == 'alpa' || $s['status'] == 'alpha') $data['ringkasan']['alpa']++;
            }
        }

        $this->view('templates/header', $data);
        $this->view('wali_kelas/dashboard', $data);
        $this->view('templates/footer', $data);
    }

    public function absensi() {
        $this->checkHasKelas();
        $data['judul'] = 'Riwayat Absensi Kelas';
        $data['kelas'] = $this->kelasBinaan;
        
        $limit = 15;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        // Filters
        $tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : '';
        $nama = isset($_GET['nama']) ? $_GET['nama'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        $waliKelasModel = $this->model('WaliKelasModel');
        $data['absensi'] = $waliKelasModel->getAbsensiDenganFilter($this->kelasBinaan['id'], $limit, $offset, $tanggal, $nama, $status);
        $totalData = $waliKelasModel->countAbsensiDenganFilter($this->kelasBinaan['id'], $tanggal, $nama, $status);
        
        $data['totalPages'] = ceil($totalData / $limit);
        $data['currentPage'] = $page;
        
        // Pass back filters to view
        $data['filters'] = [
            'tanggal' => $tanggal,
            'nama' => $nama,
            'status' => $status
        ];

        $this->view('templates/header', $data);
        $this->view('wali_kelas/absensi', $data);
        $this->view('templates/footer', $data);
    }

    public function detail($siswa_id) {
        $this->checkHasKelas();
        $data['judul'] = 'Detail Siswa';
        $data['kelas'] = $this->kelasBinaan;
        
        $siswaModel = $this->model('SiswaModel');
        $siswa = $siswaModel->getSiswaById($siswa_id);
        
        // Pastikan siswa ini benar-benar anggota kelasnya
        if (!$siswa || $siswa['kelas_id'] != $this->kelasBinaan['id']) {
            Flasher::setFlash('Akses Ditolak!', 'Anda tidak memiliki hak untuk melihat data siswa ini.', 'danger');
            $this->redirect('/wali-kelas/absensi');
        }
        
        $data['siswa'] = $siswa;
        
        $waliKelasModel = $this->model('WaliKelasModel');
        $data['rekap'] = $waliKelasModel->getRekapSiswa($siswa_id);
        
        $absensiModel = $this->model('AbsensiSiswaModel');
        $data['riwayat'] = $absensiModel->getRiwayatAbsensi($siswa_id, 30); // 30 riwayat terakhir

        $this->view('templates/header', $data);
        $this->view('wali_kelas/detail', $data);
        $this->view('templates/footer', $data);
    }

    public function inputManual() {
        $this->checkHasKelas();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            $siswa_id = $_POST['siswa_id'];
            $tanggal = $_POST['tanggal'];
            $status = $_POST['status'];
            $jam = date('H:i:s'); // Jam saat ini atau default jam masuk?
            
            // Verifikasi siswa milik kelasnya
            $siswaModel = $this->model('SiswaModel');
            $siswa = $siswaModel->getSiswaById($siswa_id);
            if (!$siswa || $siswa['kelas_id'] != $this->kelasBinaan['id']) {
                Flasher::setFlash('Akses Ditolak!', 'Siswa tidak valid.', 'danger');
                $this->redirect('/wali-kelas/dashboard');
            }
            
            $absensiModel = $this->model('AbsensiSiswaModel');
            
            // Cek duplikasi
            if ($absensiModel->cekAbsensiHariIni($siswa_id, $tanggal)) {
                Flasher::setFlash('Gagal!', 'Siswa ini sudah memiliki catatan absensi pada tanggal tersebut.', 'warning');
            } else {
                $dataAbsensi = [
                    'siswa_id' => $siswa_id,
                    'tanggal' => $tanggal,
                    'jam' => $jam,
                    'status' => $status
                ];
                
                if ($absensiModel->tambahAbsensi($dataAbsensi) > 0) {
                    Flasher::setFlash('Berhasil!', 'Absensi manual (' . ucfirst($status) . ') berhasil disimpan.', 'success');
                } else {
                    Flasher::setFlash('Gagal!', 'Terjadi kesalahan sistem.', 'danger');
                }
            }
            $this->redirect('/wali-kelas/dashboard');
        }
    }
}
