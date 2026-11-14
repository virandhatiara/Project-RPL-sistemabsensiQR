<?php
class AdminKelasController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->checkAuth(['admin']);
    }

    public function index() {
        $data['judul'] = 'Data Kelas';
        
        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        
        $kelasModel = $this->model('KelasModel');
        $data['kelas'] = $kelasModel->getAllKelas($limit, $offset, $keyword);
        $totalData = $kelasModel->countKelas($keyword);
        
        $data['totalPages'] = ceil($totalData / $limit);
        $data['currentPage'] = $page;
        $data['keyword'] = $keyword;

        $this->view('templates/header', $data);
        $this->view('admin/kelas/index', $data);
        $this->view('templates/footer', $data);
    }

    public function create() {
        $data['judul'] = 'Tambah Data Kelas';
        // Need to get list of guru for wali_kelas dropdown
        // Assuming we have a method to get all guru list without pagination
        $guruModel = $this->model('GuruModel');
        $data['guru'] = $guruModel->getAllGuru(1000, 0); // Hacky way to get all guru
        
        $this->view('templates/header', $data);
        $this->view('admin/kelas/create', $data);
        $this->view('templates/footer', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            $kelasModel = $this->model('KelasModel');

            // Sanitize
            $_POST['nama_kelas'] = trim(htmlspecialchars($_POST['nama_kelas'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['wali_kelas_id'] = (int)($_POST['wali_kelas_id'] ?? 0);

            if (empty($_POST['nama_kelas'])) {
                Flasher::setFlash('Gagal!', 'Nama Kelas wajib diisi.', 'danger');
                $this->redirect('/admin-kelas/create');
            }

            if ($kelasModel->getKelasByName($_POST['nama_kelas'])) {
                Flasher::setFlash('Gagal!', 'Nama Kelas sudah terdaftar.', 'danger');
                $this->redirect('/admin-kelas/create');
            }

            if ($kelasModel->addKelas($_POST) > 0) {
                Flasher::setFlash('Berhasil!', 'Data kelas ditambahkan.', 'success');
                $this->redirect('/admin-kelas');
            } else {
                Flasher::setFlash('Gagal!', 'Data kelas gagal ditambahkan.', 'danger');
                $this->redirect('/admin-kelas/create');
            }
        }
    }

    public function edit($id) {
        $data['judul'] = 'Edit Data Kelas';
        $data['kelas'] = $this->model('KelasModel')->getKelasById($id);
        
        if (!$data['kelas']) {
            $this->redirect('/admin-kelas');
        }

        $guruModel = $this->model('GuruModel');
        $data['guru'] = $guruModel->getAllGuru(1000, 0);

        $this->view('templates/header', $data);
        $this->view('admin/kelas/edit', $data);
        $this->view('templates/footer', $data);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            $kelasModel = $this->model('KelasModel');

            // Sanitize & validate
            $_POST['id'] = (int)($_POST['id'] ?? 0);
            $_POST['nama_kelas'] = trim(htmlspecialchars($_POST['nama_kelas'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['wali_kelas_id'] = (int)($_POST['wali_kelas_id'] ?? 0);

            if ($_POST['id'] <= 0 || empty($_POST['nama_kelas'])) {
                Flasher::setFlash('Gagal!', 'Input tidak valid.', 'danger');
                $this->redirect('/admin-kelas');
            }

            // Verify kelas exists (prevent IDOR)
            if (!$kelasModel->getKelasById($_POST['id'])) {
                Flasher::setFlash('Gagal!', 'Data kelas tidak ditemukan.', 'danger');
                $this->redirect('/admin-kelas');
            }

            if ($kelasModel->getKelasByName($_POST['nama_kelas'], $_POST['id'])) {
                Flasher::setFlash('Gagal!', 'Nama Kelas sudah terdaftar.', 'danger');
                $this->redirect('/admin-kelas/edit/' . $_POST['id']);
            }

            if ($kelasModel->updateKelas($_POST)) {
                Flasher::setFlash('Berhasil!', 'Data kelas diperbarui.', 'success');
                $this->redirect('/admin-kelas');
            } else {
                Flasher::setFlash('Gagal!', 'Data kelas gagal diperbarui.', 'danger');
                $this->redirect('/admin-kelas/edit/' . $_POST['id']);
            }
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin-kelas');
        }
        Csrf::validate();

        $kelasModel = $this->model('KelasModel');
        if ($kelasModel->deleteKelas($id)) {
            Flasher::setFlash('Berhasil!', 'Data kelas dihapus.', 'success');
        } else {
            Flasher::setFlash('Gagal!', 'Data kelas gagal dihapus.', 'danger');
        }
        $this->redirect('/admin-kelas');
    }
}
