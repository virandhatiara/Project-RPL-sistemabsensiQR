<?php
class AdminJadwalController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->checkAuth(['admin']);
    }

    public function index() {
        $data['judul'] = 'Data Jadwal';
        
        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        
        $jadwalModel = $this->model('JadwalModel');
        $data['jadwal'] = $jadwalModel->getAllJadwal($limit, $offset, $keyword);
        $totalData = $jadwalModel->countJadwal($keyword);
        
        $data['totalPages'] = ceil($totalData / $limit);
        $data['currentPage'] = $page;
        $data['keyword'] = $keyword;

        $this->view('templates/header', $data);
        $this->view('admin/jadwal/index', $data);
        $this->view('templates/footer', $data);
    }

    public function create() {
        $data['judul'] = 'Tambah Data Jadwal';
        
        $data['kelas'] = $this->model('KelasModel')->getAllKelasList();
        $data['guru'] = $this->model('GuruModel')->getAllGuru(1000, 0);

        $this->view('templates/header', $data);
        $this->view('admin/jadwal/create', $data);
        $this->view('templates/footer', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            $jadwalModel = $this->model('JadwalModel');
            
            // Sanitasi input
            $_POST['mata_pelajaran'] = trim(htmlspecialchars($_POST['mata_pelajaran'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['kelas_id']       = (int)($_POST['kelas_id'] ?? 0);
            $_POST['guru_id']        = (int)($_POST['guru_id'] ?? 0);
            $_POST['hari']           = trim($_POST['hari'] ?? '');
            $_POST['jam_mulai']      = trim($_POST['jam_mulai'] ?? '');
            $_POST['jam_selesai']    = trim($_POST['jam_selesai'] ?? '');

            if (empty($_POST['mata_pelajaran']) || empty($_POST['kelas_id']) || empty($_POST['guru_id']) || empty($_POST['hari']) || empty($_POST['jam_mulai']) || empty($_POST['jam_selesai'])) {
                Flasher::setFlash('Gagal!', 'Semua field termasuk nama mata pelajaran wajib diisi.', 'danger');
                $this->redirect('/admin-jadwal/create');
            }

            if ($jadwalModel->addJadwal($_POST) > 0) {
                Flasher::setFlash('Berhasil!', 'Data jadwal dan mata pelajaran berhasil ditambahkan.', 'success');
                $this->redirect('/admin-jadwal');
            } else {
                Flasher::setFlash('Gagal!', 'Data jadwal gagal ditambahkan.', 'danger');
                $this->redirect('/admin-jadwal/create');
            }
        }
    }

    public function edit($id) {
        $data['judul'] = 'Edit Data Jadwal';
        $data['jadwal'] = $this->model('JadwalModel')->getJadwalById($id);
        
        if (!$data['jadwal']) {
            $this->redirect('/admin-jadwal');
        }

        $data['kelas'] = $this->model('KelasModel')->getAllKelasList();
        $data['guru'] = $this->model('GuruModel')->getAllGuru(1000, 0);

        $this->view('templates/header', $data);
        $this->view('admin/jadwal/edit', $data);
        $this->view('templates/footer', $data);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            $jadwalModel = $this->model('JadwalModel');
            
            // Sanitasi input
            $_POST['id']             = (int)($_POST['id'] ?? 0);
            $_POST['mata_pelajaran'] = trim(htmlspecialchars($_POST['mata_pelajaran'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['kelas_id']       = (int)($_POST['kelas_id'] ?? 0);
            $_POST['guru_id']        = (int)($_POST['guru_id'] ?? 0);
            $_POST['hari']           = trim($_POST['hari'] ?? '');
            $_POST['jam_mulai']      = trim($_POST['jam_mulai'] ?? '');
            $_POST['jam_selesai']    = trim($_POST['jam_selesai'] ?? '');

            if (empty($_POST['id']) || empty($_POST['mata_pelajaran']) || empty($_POST['kelas_id']) || empty($_POST['guru_id']) || empty($_POST['hari']) || empty($_POST['jam_mulai']) || empty($_POST['jam_selesai'])) {
                Flasher::setFlash('Gagal!', 'Semua field termasuk nama mata pelajaran wajib diisi.', 'danger');
                $this->redirect('/admin-jadwal/edit/' . (int)$_POST['id']);
            }

            if ($jadwalModel->updateJadwal($_POST)) {
                Flasher::setFlash('Berhasil!', 'Data jadwal dan mata pelajaran berhasil diperbarui.', 'success');
                $this->redirect('/admin-jadwal');
            } else {
                Flasher::setFlash('Gagal!', 'Data jadwal gagal diperbarui.', 'danger');
                $this->redirect('/admin-jadwal/edit/' . (int)$_POST['id']);
            }
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin-jadwal');
        }
        Csrf::validate();

        $jadwalModel = $this->model('JadwalModel');
        if ($jadwalModel->deleteJadwal($id)) {
            Flasher::setFlash('Berhasil!', 'Data jadwal dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Data jadwal gagal dihapus.', 'danger');
        }
        $this->redirect('/admin-jadwal');
    }
}
