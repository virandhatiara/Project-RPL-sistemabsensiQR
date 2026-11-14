<?php
class Controller {
    public function __construct() {
        // Always ensure CSRF token exists in session for use in view forms
        Csrf::getToken();

        // Global CSRF Check for POST requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::validate();
        }
    }

    public function view($view, $data = []) {
        if (file_exists('../views/' . $view . '.php')) {
            require_once '../views/' . $view . '.php';
        } else {
            die("View does not exist: " . $view);
        }
    }

    public function model($model) {
        if (file_exists('../models/' . $model . '.php')) {
            require_once '../models/' . $model . '.php';
            return new $model;
        } else {
            die("Model does not exist: " . $model);
        }
    }

    public function redirect($url) {
        header('Location: ' . BASEURL . $url);
        exit;
    }

    public function checkAuth($allowedRoles = []) {
        // If not logged in
        if (!isset($_SESSION['user_id'])) {
            Flasher::setFlash('Silakan', 'login terlebih dahulu.', 'warning');
            $this->redirect('/auth');
        }

        // Check role
        if (!empty($allowedRoles) && !in_array($_SESSION['role'], $allowedRoles)) {
            Flasher::setFlash('Akses ditolak!', 'Anda tidak memiliki hak akses ke halaman ini.', 'danger');
            
            // Redirect based on current role
            $role = $_SESSION['role'];
            $roleUrl = str_replace('_', '-', $role); // kepala_sekolah -> kepala-sekolah
            $this->redirect('/' . $roleUrl . '/dashboard');
        }
    }
}
