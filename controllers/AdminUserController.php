<?php
class AdminUserController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->checkAuth(['admin']);
    }

    public function index() {
        $userModel = $this->model('UserModel');
        
        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        if ($page < 1) $page = 1;
        $offset = ($page - 1) * $limit;
        
        $keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
        
        $totalUsers = $userModel->countUser($keyword);
        $totalPages = ceil($totalUsers / $limit);
        
        $users = $userModel->getAllUser($limit, $offset, $keyword);
        
        $data['judul'] = 'Manajemen User & Admin';
        $data['users'] = $users;
        $data['currentPage'] = $page;
        $data['totalPages'] = $totalPages;
        $data['totalUsers'] = $totalUsers;
        $data['keyword'] = $keyword;
        
        $this->view('templates/header', $data);
        $this->view('admin/user/index', $data);
        $this->view('templates/footer', $data);
    }

    public function create() {
        $data['judul'] = 'Tambah User & Admin';
        
        $this->view('templates/header', $data);
        $this->view('admin/user/create', $data);
        $this->view('templates/footer', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            
            $username  = trim($_POST['username'] ?? '');
            $nama      = trim($_POST['nama'] ?? '');
            $password  = $_POST['password'] ?? '';
            $role      = $_POST['role'] ?? '';
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            
            if (empty($username) || empty($password) || empty($role)) {
                Flasher::setFlash('Gagal!', 'Username, password, dan role wajib diisi.', 'danger');
                $this->redirect('/admin-user/create');
            }
            
            if (strlen($password) < 6) {
                Flasher::setFlash('Gagal!', 'Password minimal 6 karakter.', 'danger');
                $this->redirect('/admin-user/create');
            }
            
            $userModel = $this->model('UserModel');
            if ($userModel->getUserByUsername($username)) {
                Flasher::setFlash('Gagal!', 'Username "' . htmlspecialchars($username) . '" sudah digunakan. Gunakan username lain.', 'danger');
                $this->redirect('/admin-user/create');
            }
            
            $insertId = $userModel->addUser([
                'username'  => $username,
                'nama'      => $nama,
                'password'  => $password,
                'role'      => $role,
                'is_active' => $is_active
            ]);
            
            if ($insertId) {
                Flasher::setFlash('Berhasil!', 'User / Admin baru atas nama "' . htmlspecialchars($nama ?: $username) . '" berhasil ditambahkan.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Gagal menambahkan user baru.', 'danger');
            }
            
            $this->redirect('/admin-user');
        } else {
            $this->redirect('/admin-user');
        }
    }

    public function edit($id) {
        $userModel = $this->model('UserModel');
        $user = $userModel->getUserById($id);
        
        if (!$user) {
            Flasher::setFlash('Gagal!', 'Data user tidak ditemukan.', 'danger');
            $this->redirect('/admin-user');
        }
        
        $data['judul'] = 'Edit User & Admin';
        $data['user'] = $user;
        
        $this->view('templates/header', $data);
        $this->view('admin/user/edit', $data);
        $this->view('templates/footer', $data);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            
            $id        = (int)($_POST['id'] ?? 0);
            $username  = trim($_POST['username'] ?? '');
            $nama      = trim($_POST['nama'] ?? '');
            $password  = $_POST['password'] ?? '';
            $role      = $_POST['role'] ?? '';
            $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
            
            $userModel = $this->model('UserModel');
            $currentUser = $userModel->getUserById($id);
            
            if (!$currentUser) {
                Flasher::setFlash('Gagal!', 'Data user tidak ditemukan.', 'danger');
                $this->redirect('/admin-user');
            }
            
            // Jika mengedit akun sendiri, pertahankan role dan status aktif
            if ($id == $_SESSION['user_id']) {
                $role = $currentUser['role'];
                $is_active = $currentUser['is_active'];
            }
            
            if (empty($username) || empty($role)) {
                Flasher::setFlash('Gagal!', 'Username dan role wajib diisi.', 'danger');
                $this->redirect('/admin-user/edit/' . $id);
            }
            
            if (!empty($password) && strlen($password) < 6) {
                Flasher::setFlash('Gagal!', 'Password baru minimal 6 karakter.', 'danger');
                $this->redirect('/admin-user/edit/' . $id);
            }
            
            // Cek apakah username dipakai orang lain
            if ($userModel->getUserByUsername($username, $id)) {
                Flasher::setFlash('Gagal!', 'Username "' . htmlspecialchars($username) . '" sudah digunakan oleh akun lain.', 'danger');
                $this->redirect('/admin-user/edit/' . $id);
            }
            
            $updateData = [
                'id'        => $id,
                'username'  => $username,
                'nama'      => $nama,
                'role'      => $role,
                'is_active' => $is_active
            ];
            
            if (!empty($password)) {
                $updateData['password'] = $password;
            }
            
            if ($userModel->updateUser($updateData)) {
                // Update session jika mengedit akun diri sendiri
                if ($id == $_SESSION['user_id']) {
                    $_SESSION['username'] = $username;
                    $_SESSION['nama']     = $nama ?: $username;
                }
                
                Flasher::setFlash('Berhasil!', 'Data user / admin berhasil diperbarui.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Gagal memperbarui data user.', 'danger');
            }
            
            $this->redirect('/admin-user');
        } else {
            $this->redirect('/admin-user');
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            
            $id = (int)$id;
            
            // Cegah hapus diri sendiri
            if ($id == $_SESSION['user_id']) {
                Flasher::setFlash('Gagal!', 'Anda tidak dapat menghapus akun Anda sendiri.', 'danger');
                $this->redirect('/admin-user');
            }
            
            $userModel = $this->model('UserModel');
            if ($userModel->deleteUser($id)) {
                Flasher::setFlash('Berhasil!', 'Akun user berhasil dihapus.', 'success');
            } else {
                Flasher::setFlash('Gagal!', 'Gagal menghapus user.', 'danger');
            }
            
            $this->redirect('/admin-user');
        } else {
            $this->redirect('/admin-user');
        }
    }
}
