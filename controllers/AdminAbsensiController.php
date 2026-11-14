<?php
class AdminAbsensiController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->checkAuth(['admin']);
    }

    public function index() {
        $data['judul'] = 'Data Absensi Siswa';

        // Filter Parameters
        $tanggal  = isset($_GET['tanggal']) && !empty($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');
        $kelas_id = isset($_GET['kelas_id']) && (int)$_GET['kelas_id'] > 0 ? (int)$_GET['kelas_id'] : null;
        $status   = (isset($_GET['status']) && $_GET['status'] !== '') ? (int)$_GET['status'] : null;
        $keyword  = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

        // Pagination
        $limit  = 15;
        $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;

        $absensiModel = $this->model('AbsensiSiswaModel');
        $siswaModel   = $this->model('SiswaModel');
        $kelasModel   = $this->model('KelasModel');

        // Otomatis masukkan siswa dan guru yang belum absen sebagai Alpha jika tanggal <= hari ini
        if ($tanggal <= date('Y-m-d')) {
            $absensiModel->autoDataAlphaSiswa($tanggal);
            $this->model('AbsensiGuruModel')->autoDataAlphaGuru($tanggal);
        }

        // Query data
        $data['absensi']    = $absensiModel->getAbsensiFiltered($tanggal, $kelas_id, $status, $keyword, $limit, $offset);
        $totalData          = $absensiModel->countAbsensiFiltered($tanggal, $kelas_id, $status, $keyword);
        $data['statistik']  = $absensiModel->getStatistikKehadiran($tanggal, $kelas_id);
        $data['kelas_list'] = $kelasModel->getAllKelasList();
        $data['siswa_list'] = $siswaModel->getAllSiswaList();

        $data['totalPages']  = ceil($totalData / $limit);
        $data['currentPage'] = $page;
        $data['totalData']   = $totalData;
        $data['tanggal']     = $tanggal;
        $data['kelas_id']    = $kelas_id;
        $data['status']      = $status;
        $data['keyword']     = $keyword;

        $this->view('templates/header', $data);
        $this->view('admin/absensi/index', $data);
        $this->view('templates/footer', $data);
    }

    /**
     * Tambah Absensi Siswa Manual oleh Admin
     * Aturan integer: 0 = Hadir, 1 = Sakit, 2 = Izin, 3 = Alpha
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin-absensi');
        }

        Csrf::validate();

        $siswa_id  = (int)($_POST['siswa_id'] ?? 0);
        $tanggal   = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $jam       = trim($_POST['jam'] ?? date('H:i:s'));
        $rawStatus = $_POST['status'] ?? 0;

        if (is_numeric($rawStatus)) {
            $status = (int)$rawStatus;
        } else {
            $map = ['hadir' => 0, 'sakit' => 1, 'izin' => 2, 'alpa' => 3, 'alpha' => 3, 'terlambat' => 0];
            $status = $map[strtolower(trim($rawStatus))] ?? 0;
        }
        if ($status < 0 || $status > 3) {
            $status = 0;
        }

        if ($siswa_id <= 0 || empty($tanggal)) {
            Flasher::setFlash('Gagal!', 'Pilih siswa dan tentukan tanggal absensi dengan benar.', 'danger');
            $this->redirect('/admin-absensi?tanggal=' . urlencode($tanggal));
        }

        // Lengkapi format jam jika hanya HH:MM
        if (strlen($jam) === 5) {
            $jam .= ':00';
        }

        $absensiModel = $this->model('AbsensiSiswaModel');
        $saveResult = $absensiModel->tambahOrUpdateAbsensi([
            'siswa_id' => $siswa_id,
            'tanggal'  => $tanggal,
            'jam'      => $jam,
            'status'   => $status
        ]);

        if ($saveResult) {
            switch ($status) {
                case 1:  $labelStatus = 'Sakit'; break;
                case 2:  $labelStatus = 'Izin';  break;
                case 3:  $labelStatus = 'Alpha'; break;
                case 0:
                default: $labelStatus = 'Hadir'; break;
            }
            Flasher::setFlash('Berhasil!', "Data absensi siswa dengan status {$labelStatus} berhasil disimpan.", 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Terjadi kesalahan saat menyimpan data absensi.', 'danger');
        }

        $this->redirect('/admin-absensi?tanggal=' . urlencode($tanggal));
    }

    /**
     * Perbarui Status Absensi oleh Admin
     * Aturan integer: 0 = Hadir, 1 = Sakit, 2 = Izin, 3 = Alpha
     */
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin-absensi');
        }

        Csrf::validate();

        $id        = (int)($_POST['id'] ?? 0);
        $tanggal   = trim($_POST['tanggal'] ?? date('Y-m-d'));
        $jam       = trim($_POST['jam'] ?? date('H:i:s'));
        $rawStatus = $_POST['status'] ?? 0;

        if (is_numeric($rawStatus)) {
            $status = (int)$rawStatus;
        } else {
            $map = ['hadir' => 0, 'sakit' => 1, 'izin' => 2, 'alpa' => 3, 'alpha' => 3, 'terlambat' => 0];
            $status = $map[strtolower(trim($rawStatus))] ?? 0;
        }
        if ($status < 0 || $status > 3) {
            $status = 0;
        }

        if ($id <= 0) {
            Flasher::setFlash('Gagal!', 'ID absensi tidak valid.', 'danger');
            $this->redirect('/admin-absensi');
        }

        if (strlen($jam) === 5) {
            $jam .= ':00';
        }

        $absensiModel = $this->model('AbsensiSiswaModel');
        $success = $absensiModel->updateAbsensi($id, [
            'tanggal' => $tanggal,
            'jam'     => $jam,
            'status'  => $status
        ]);

        if ($success) {
            switch ($status) {
                case 1:  $labelStatus = 'Sakit'; break;
                case 2:  $labelStatus = 'Izin';  break;
                case 3:  $labelStatus = 'Alpha'; break;
                case 0:
                default: $labelStatus = 'Hadir'; break;
            }
            Flasher::setFlash('Berhasil!', "Status kehadiran berhasil diubah menjadi {$labelStatus}.", 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Gagal memperbarui status absensi.', 'danger');
        }

        $this->redirect('/admin-absensi?tanggal=' . urlencode($tanggal));
    }

    /**
     * Hapus Data Absensi
     */
    public function delete($id = null) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin-absensi');
        }

        Csrf::validate();

        $id = (int)$id;
        $tanggal = trim($_POST['tanggal'] ?? date('Y-m-d'));

        if ($id > 0) {
            $absensiModel = $this->model('AbsensiSiswaModel');
            $absensiModel->deleteAbsensi($id);
            Flasher::setFlash('Berhasil!', 'Data absensi siswa berhasil dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal!', 'ID absensi tidak ditemukan.', 'danger');
        }

        $this->redirect('/admin-absensi?tanggal=' . urlencode($tanggal));
    }

    /**
     * Sinkronisasi data Alpha otomatis untuk siswa & guru yang belum absen
     */
    public function sinkronisasiAlpha() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin-absensi');
        }

        Csrf::validate();

        $tanggal = isset($_POST['tanggal']) && !empty($_POST['tanggal']) ? $_POST['tanggal'] : date('Y-m-d');
        $absensiSiswaModel = $this->model('AbsensiSiswaModel');
        $absensiGuruModel  = $this->model('AbsensiGuruModel');

        $siswaCount = $absensiSiswaModel->autoDataAlphaSiswa($tanggal);
        $guruCount  = $absensiGuruModel->autoDataAlphaGuru($tanggal);

        Flasher::setFlash('Berhasil!', "Sinkronisasi Alpha selesai. Ditambahkan {$siswaCount} siswa dan {$guruCount} guru yang belum absen ke status Alpha.", 'success');
        $this->redirect('/admin-absensi?tanggal=' . urlencode($tanggal));
    }
}
