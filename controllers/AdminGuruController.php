<?php
class AdminGuruController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->checkAuth(['admin']);
    }

    public function index() {
        $data['judul'] = 'Data Guru';
        
        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        
        $guruModel = $this->model('GuruModel');
        $data['guru'] = $guruModel->getAllGuru($limit, $offset, $keyword);
        $totalData = $guruModel->countGuru($keyword);
        
        $data['totalPages'] = ceil($totalData / $limit);
        $data['currentPage'] = $page;
        $data['keyword'] = $keyword;

        $this->view('templates/header', $data);
        $this->view('admin/guru/index', $data);
        $this->view('templates/footer', $data);
    }

    public function create() {
        $data['judul'] = 'Tambah Data Guru';
        
        $this->view('templates/header', $data);
        $this->view('admin/guru/create', $data);
        $this->view('templates/footer', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            $guruModel = $this->model('GuruModel');

            // --- Sanitize Inputs ---
            $_POST['nip']      = trim(htmlspecialchars($_POST['nip'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['nama']     = trim(htmlspecialchars($_POST['nama'] ?? '', ENT_QUOTES, 'UTF-8'));
            // Hapus sebutan 'Bapak', 'Ibu', 'Pak', 'Bu' di awal nama guru
            $_POST['nama']     = trim(preg_replace('/^(bapak|ibu|pak|bu)\.?\s+/i', '', $_POST['nama']));
            $_POST['username'] = trim(htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['password'] = trim($_POST['password'] ?? '');

            // Sanitasi Nomor HP / WhatsApp
            $rawNoHp = trim($_POST['no_hp'] ?? ($_POST['no_wa'] ?? ''));
            $cleanNoHp = preg_replace('/[^0-9]/', '', $rawNoHp);
            $_POST['no_wa'] = !empty($cleanNoHp) ? $cleanNoHp : null;
            $_POST['no_hp'] = !empty($cleanNoHp) ? $cleanNoHp : null;

            if (empty($_POST['username'])) {
                $_POST['username'] = $_POST['nip'];
            }

            // --- Validate required fields ---
            if (empty($_POST['nip']) || empty($_POST['nama'])) {
                Flasher::setFlash('Gagal!', 'NIP dan Nama wajib diisi.', 'danger');
                $this->redirect('/admin-guru/create');
            }

            if (empty($_POST['password'])) {
                Flasher::setFlash('Gagal!', 'Password akun login guru wajib diisi.', 'danger');
                $this->redirect('/admin-guru/create');
            }

            if ($guruModel->getGuruByNip($_POST['nip'])) {
                Flasher::setFlash('Gagal!', 'NIP sudah terdaftar.', 'danger');
                $this->redirect('/admin-guru/create');
            }

            // Check if username already exists
            $userModel = $this->model('UserModel');
            if ($userModel->getUserByUsername($_POST['username'])) {
                Flasher::setFlash('Gagal!', 'Username "' . $_POST['username'] . '" sudah digunakan oleh akun lain.', 'danger');
                $this->redirect('/admin-guru/create');
            }

            $userId = $userModel->addUser([
                'username'  => $_POST['username'],
                'password'  => $_POST['password'],
                'role'      => 'guru',
                'is_active' => 1
            ]);

            $_POST['user_id'] = $userId;
            // Generate QR Code otomatis
            if (empty($_POST['barcode_id'])) {
                $_POST['barcode_id'] = 'QR-G-' . $_POST['nip'] . '-' . bin2hex(random_bytes(3));
            }

            if ($guruModel->addGuru($_POST) > 0) {
                Flasher::setFlash('Berhasil!', 'Data guru dan akun login berhasil ditambahkan.', 'success');
                $this->redirect('/admin-guru');
            } else {
                Flasher::setFlash('Gagal!', 'Data guru gagal ditambahkan.', 'danger');
                $this->redirect('/admin-guru/create');
            }
        }
    }

    public function edit($id) {
        $data['judul'] = 'Edit Data Guru';
        $data['guru'] = $this->model('GuruModel')->getGuruById($id);
        
        if (!$data['guru']) {
            $this->redirect('/admin-guru');
        }

        $this->view('templates/header', $data);
        $this->view('admin/guru/edit', $data);
        $this->view('templates/footer', $data);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            $guruModel = $this->model('GuruModel');

            // --- Sanitize & Validate Inputs ---
            $_POST['id']       = (int)($_POST['id'] ?? 0);
            $_POST['nip']      = trim(htmlspecialchars($_POST['nip'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['nama']     = trim(htmlspecialchars($_POST['nama'] ?? '', ENT_QUOTES, 'UTF-8'));
            // Hapus sebutan 'Bapak', 'Ibu', 'Pak', 'Bu' di awal nama guru
            $_POST['nama']     = trim(preg_replace('/^(bapak|ibu|pak|bu)\.?\s+/i', '', $_POST['nama']));
            $_POST['username'] = trim(htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8'));
            $_POST['password'] = trim($_POST['password'] ?? '');

            if ($_POST['id'] <= 0 || empty($_POST['nip']) || empty($_POST['nama'])) {
                Flasher::setFlash('Gagal!', 'Input tidak valid.', 'danger');
                $this->redirect('/admin-guru');
            }

            if (empty($_POST['username'])) {
                $_POST['username'] = $_POST['nip'];
            }

            // Verify guru record exists (prevent IDOR)
            $currentGuru = $guruModel->getGuruById($_POST['id']);
            if (!$currentGuru) {
                Flasher::setFlash('Gagal!', 'Data guru tidak ditemukan.', 'danger');
                $this->redirect('/admin-guru');
            }

            if ($guruModel->getGuruByNip($_POST['nip'], $_POST['id'])) {
                Flasher::setFlash('Gagal!', 'NIP sudah terdaftar pada guru lain.', 'danger');
                $this->redirect('/admin-guru/edit/' . $_POST['id']);
            }

            // Check if username already exists for other users
            $userModel = $this->model('UserModel');
            if ($userModel->getUserByUsername($_POST['username'], $currentGuru['user_id'])) {
                Flasher::setFlash('Gagal!', 'Username "' . $_POST['username'] . '" sudah digunakan oleh akun lain.', 'danger');
                $this->redirect('/admin-guru/edit/' . $_POST['id']);
            }

            // Sync user account
            $userId = $currentGuru['user_id'];
            if ($userId) {
                $userData = [
                    'id'        => $userId,
                    'username'  => $_POST['username'],
                    'role'      => 'guru',
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
                    'role'      => 'guru',
                    'is_active' => 1
                ]);
            }

            $_POST['user_id'] = $userId;
            $_POST['barcode_id'] = !empty($_POST['barcode_id']) ? trim($_POST['barcode_id']) : $currentGuru['barcode_id'];

            // Sanitasi Nomor HP / WhatsApp
            $rawNoHp = trim($_POST['no_hp'] ?? ($_POST['no_wa'] ?? ''));
            $cleanNoHp = preg_replace('/[^0-9]/', '', $rawNoHp);
            $_POST['no_wa'] = !empty($cleanNoHp) ? $cleanNoHp : null;
            $_POST['no_hp'] = !empty($cleanNoHp) ? $cleanNoHp : null;

            if ($guruModel->updateGuru($_POST)) {
                Flasher::setFlash('Berhasil!', 'Data guru dan akun login diperbarui.', 'success');
                $this->redirect('/admin-guru');
            } else {
                Flasher::setFlash('Gagal!', 'Data guru gagal diperbarui.', 'danger');
                $this->redirect('/admin-guru/edit/' . $_POST['id']);
            }
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin-guru');
        }
        Csrf::validate();

        $guruModel = $this->model('GuruModel');
        $guru = $guruModel->getGuruById($id);
        
        if ($guru) {
            if ($guruModel->deleteGuru($id)) {
                if ($guru['user_id']) {
                    $this->model('UserModel')->deleteUser($guru['user_id']);
                }
                Flasher::setFlash('Berhasil!', 'Data guru dihapus.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Data guru gagal dihapus.', 'danger');
            }
        }
        $this->redirect('/admin-guru');
    }
}
