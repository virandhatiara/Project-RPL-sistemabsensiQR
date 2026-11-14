<?php
class AuthController extends Controller {

    public function __construct() {
        // Skip global CSRF check for auth controller.
        // The login() method validates CSRF itself explicitly.
        // We do NOT call parent::__construct() here on purpose.
        // Generate a CSRF token if none exists yet.
        Csrf::getToken();
    }

    public function index() {
        // If already logged in, redirect based on role
        if (isset($_SESSION['user_id'])) {
            $roleUrl = str_replace('_', '-', $_SESSION['role']);
            $this->redirect('/' . $roleUrl . '/dashboard');
        }

        $data['judul'] = 'Login Sistem Absensi';
        
        $this->view('templates/header', $data);
        $this->view('auth/login', $data);
        $this->view('templates/footer', $data);
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $userModel = $this->model('User');
            $user = $userModel->getUserByUsername($username);

            if ($user) {
                // Check if account is active
                if ($user['is_active'] == 0) {
                    Flasher::setFlash('Login Gagal!', 'Akun Anda tidak aktif.', 'danger');
                    $this->redirect('/auth');
                }

                // Verify Password
                if (password_verify($password, $user['password'])) {
                    // Prevent Session Fixation
                    session_regenerate_id(true);

                    // Set Session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    // Cari nama pemegang akun
                    $namaLengkap = !empty($user['nama']) ? $user['nama'] : '';
                    if (empty($namaLengkap)) {
                        if ($user['role'] === 'guru' || $user['role'] === 'wali_kelas') {
                            $db = new Database();
                            $db->query("SELECT nama FROM guru WHERE user_id = :uid LIMIT 1");
                            $db->bind('uid', $user['id']);
                            $g = $db->single();
                            if ($g && !empty($g['nama'])) $namaLengkap = $g['nama'];
                        } elseif ($user['role'] === 'siswa') {
                            $db = new Database();
                            $db->query("SELECT nama FROM siswa WHERE user_id = :uid LIMIT 1");
                            $db->bind('uid', $user['id']);
                            $s = $db->single();
                            if ($s && !empty($s['nama'])) $namaLengkap = $s['nama'];
                        }
                    }
                    $_SESSION['nama'] = !empty($namaLengkap) ? $namaLengkap : $user['username'];

                    // Redirect based on role
                    $roleUrl = str_replace('_', '-', $user['role']);
                    $this->redirect('/' . $roleUrl . '/dashboard');
                } else {
                    Flasher::setFlash('Login Gagal!', 'Password yang Anda masukkan salah.', 'danger');
                    $this->redirect('/auth');
                }
            } else {
                Flasher::setFlash('Login Gagal!', 'Username / NIS / NIP tidak ditemukan.', 'danger');
                $this->redirect('/auth');
            }
        } else {
            $this->redirect('/auth');
        }
    }
    
    public function logout() {
        session_unset();
        session_destroy();
        $this->redirect('/auth');
    }
}
