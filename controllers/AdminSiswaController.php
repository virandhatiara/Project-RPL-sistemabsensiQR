<?php
class AdminSiswaController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->checkAuth(['admin']);
    }

    public function index() {
        $data['judul'] = 'Data Siswa';
        
        // Pagination
        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        // Search
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        
        $siswaModel = $this->model('SiswaModel');
        $data['siswa'] = $siswaModel->getAllSiswa($limit, $offset, $keyword);
        $totalData = $siswaModel->countSiswa($keyword);
        
        $data['totalPages'] = ceil($totalData / $limit);
        $data['currentPage'] = $page;
        $data['keyword'] = $keyword;

        $this->view('templates/header', $data);
        $this->view('admin/siswa/index', $data);
        $this->view('templates/footer', $data);
    }

    public function create() {
        $data['judul'] = 'Tambah Data Siswa';
        $data['kelas'] = $this->model('KelasModel')->getAllKelasList();
        
        $this->view('templates/header', $data);
        $this->view('admin/siswa/create', $data);
        $this->view('templates/footer', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            $siswaModel = $this->model('SiswaModel');

            // --- Sanitize Inputs ---
            $_POST['nis']        = trim(htmlspecialchars($_POST['nis'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['nama']       = trim(htmlspecialchars($_POST['nama'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['kelas_id']   = (int)($_POST['kelas_id'] ?? 0);
            $_POST['nama_ortu']  = trim(htmlspecialchars($_POST['nama_ortu'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['no_hp_ortu'] = trim(preg_replace('/[^0-9]/', '', $_POST['no_hp_ortu'] ?? ''));
            $_POST['username']   = trim(htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['password']   = trim($_POST['password'] ?? '');

            // Fallback username ke NIS jika tidak diisi
            if (empty($_POST['username'])) {
                $_POST['username'] = $_POST['nis'];
            }

            // --- Validate required fields ---
            if (empty($_POST['nis']) || empty($_POST['nama']) || $_POST['kelas_id'] <= 0) {
                Flasher::setFlash('Gagal!', 'NIS, Nama, dan Kelas wajib diisi.', 'danger');
                $this->redirect('/admin-siswa/create');
            }

            if (empty($_POST['password'])) {
                Flasher::setFlash('Gagal!', 'Password akun siswa wajib diisi.', 'danger');
                $this->redirect('/admin-siswa/create');
            }

            // Check if NIS already exists
            if ($siswaModel->getSiswaByNis($_POST['nis'])) {
                Flasher::setFlash('Gagal!', 'NIS sudah terdaftar.', 'danger');
                $this->redirect('/admin-siswa/create');
            }

            // Check if username already exists
            $userModel = $this->model('UserModel');
            if ($userModel->getUserByUsername($_POST['username'])) {
                Flasher::setFlash('Gagal!', 'Username "' . $_POST['username'] . '" sudah digunakan oleh akun lain.', 'danger');
                $this->redirect('/admin-siswa/create');
            }

            // Create User account with custom username & password
            $userId = $userModel->addUser([
                'username'  => $_POST['username'],
                'password'  => $_POST['password'],
                'role'      => 'siswa',
                'is_active' => 1
            ]);

            $_POST['user_id'] = $userId;
            // Generate QR Code otomatis
            $_POST['barcode_id'] = 'QR-S-' . $_POST['nis'] . '-' . bin2hex(random_bytes(3));

            if ($siswaModel->addSiswa($_POST) > 0) {
                Flasher::setFlash('Berhasil!', 'Data siswa dan akun login berhasil ditambahkan.', 'success');
                $this->redirect('/admin-siswa');
            } else {
                Flasher::setFlash('Gagal!', 'Data siswa gagal ditambahkan.', 'danger');
                $this->redirect('/admin-siswa/create');
            }
        }
    }

    public function edit($id) {
        $data['judul'] = 'Edit Data Siswa';
        $data['siswa'] = $this->model('SiswaModel')->getSiswaById($id);
        $data['kelas'] = $this->model('KelasModel')->getAllKelasList();
        
        if (!$data['siswa']) {
            $this->redirect('/admin-siswa');
        }

        $this->view('templates/header', $data);
        $this->view('admin/siswa/edit', $data);
        $this->view('templates/footer', $data);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            $siswaModel = $this->model('SiswaModel');

            // --- Sanitize & Validate Inputs ---
            $_POST['id']         = (int)($_POST['id'] ?? 0);
            $_POST['nis']        = trim(htmlspecialchars($_POST['nis'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['nama']       = trim(htmlspecialchars($_POST['nama'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['kelas_id']   = (int)($_POST['kelas_id'] ?? 0);
            $_POST['nama_ortu']  = trim(htmlspecialchars($_POST['nama_ortu'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['no_hp_ortu'] = trim(preg_replace('/[^0-9]/', '', $_POST['no_hp_ortu'] ?? ''));
            $_POST['username']   = trim(htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['password']   = trim($_POST['password'] ?? '');

            if ($_POST['id'] <= 0 || empty($_POST['nis']) || empty($_POST['nama']) || $_POST['kelas_id'] <= 0) {
                Flasher::setFlash('Gagal!', 'Input tidak valid.', 'danger');
                $this->redirect('/admin-siswa');
            }

            if (empty($_POST['username'])) {
                $_POST['username'] = $_POST['nis'];
            }

            // Verify the siswa record actually exists (prevent IDOR)
            $currentSiswa = $siswaModel->getSiswaById($_POST['id']);
            if (!$currentSiswa) {
                Flasher::setFlash('Gagal!', 'Data siswa tidak ditemukan.', 'danger');
                $this->redirect('/admin-siswa');
            }

            // Check if NIS already exists for other siswa
            if ($siswaModel->getSiswaByNis($_POST['nis'], $_POST['id'])) {
                Flasher::setFlash('Gagal!', 'NIS sudah terdaftar pada siswa lain.', 'danger');
                $this->redirect('/admin-siswa/edit/' . $_POST['id']);
            }

            // Check if username already exists for other users
            $userModel = $this->model('UserModel');
            if ($userModel->getUserByUsername($_POST['username'], $currentSiswa['user_id'])) {
                Flasher::setFlash('Gagal!', 'Username "' . $_POST['username'] . '" sudah digunakan oleh akun lain.', 'danger');
                $this->redirect('/admin-siswa/edit/' . $_POST['id']);
            }

            // Sync user account
            $userId = $currentSiswa['user_id'];
            if ($userId) {
                $userData = [
                    'id'        => $userId,
                    'username'  => $_POST['username'],
                    'role'      => 'siswa',
                    'is_active' => 1
                ];
                if (!empty($_POST['password'])) {
                    $userData['password'] = $_POST['password'];
                }
                $userModel->updateUser($userData);
            } else {
                $pass = !empty($_POST['password']) ? $_POST['password'] : 'password';
                $userId = $userModel->addUser([
                    'username'  => $_POST['username'],
                    'password'  => $pass,
                    'role'      => 'siswa',
                    'is_active' => 1
                ]);
            }

            $_POST['user_id'] = $userId;
            $_POST['barcode_id'] = !empty($_POST['barcode_id']) ? trim($_POST['barcode_id']) : $currentSiswa['barcode_id'];

            if ($siswaModel->updateSiswa($_POST)) {
                Flasher::setFlash('Berhasil!', 'Data siswa dan akun login diperbarui.', 'success');
                $this->redirect('/admin-siswa');
            } else {
                Flasher::setFlash('Gagal!', 'Data siswa gagal diperbarui.', 'danger');
                $this->redirect('/admin-siswa/edit/' . $_POST['id']);
            }
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin-siswa');
        }
        Csrf::validate();

        $siswaModel = $this->model('SiswaModel');
        $siswa = $siswaModel->getSiswaById($id);
        
        if ($siswa) {
            // Delete user account as well if necessary (depends on ON DELETE CASCADE in db)
            if ($siswaModel->deleteSiswa($id)) {
                if ($siswa['user_id']) {
                    $this->model('UserModel')->deleteUser($siswa['user_id']);
                }
                Flasher::setFlash('Berhasil!', 'Data siswa dihapus.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Data siswa gagal dihapus.', 'danger');
            }
        }
        $this->redirect('/admin-siswa');
    }
}
