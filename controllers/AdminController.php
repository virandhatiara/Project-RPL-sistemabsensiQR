<?php
class AdminController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->checkAuth(['admin']);
    }

    public function dashboard() {
        date_default_timezone_set('Asia/Jakarta');
        $tanggal = date('Y-m-d');

        $model   = $this->model('AdminDashboardModel');
        $aiModel = $this->model('AiSettingModel');

        // Otomatis catat Alpha bagi siswa dan guru yang belum mengisi absensi hadir, sakit, izin
        $this->model('AbsensiSiswaModel')->autoDataAlphaSiswa($tanggal);
        $this->model('AbsensiGuruModel')->autoDataAlphaGuru($tanggal);

        $data['judul']          = 'Dashboard Admin';
        $data['ringkasan']      = $model->getTotalRingkasan();
        $data['absensi_siswa']  = $model->getAbsensiSiswaHariIni($tanggal);
        $data['absensi_guru']   = $model->getAbsensiGuruHariIni($tanggal);
        $data['tidak_hadir']    = $model->getSiswaTidakHadir($tanggal);
        $data['tren']           = $model->getTrenKehadiran();
        $data['tanggal']        = $tanggal;
        $data['ai_setting']     = $aiModel->getSetting();
        $data['ai_presets']     = $aiModel->getPresetModels();

        $this->view('templates/header', $data);
        $this->view('admin/dashboard', $data);
        $this->view('templates/footer', $data);
    }

    /**
     * Simpan pembaruan konfigurasi AI API Key & Model
     */
    public function saveAiSetting() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/dashboard');
        }

        $aiModel = $this->model('AiSettingModel');
        $success = $aiModel->saveSetting($_POST);

        if ($success) {
            Flasher::setFlash('Konfigurasi AI Chatbot', 'berhasil disimpan dan diperbarui!', 'success');
        } else {
            Flasher::setFlash('Gagal menyimpan konfigurasi AI:', 'pastikan API Key telah diisi.', 'danger');
        }

        $this->redirect('/admin/dashboard#ai-setting-card');
    }

    /**
     * AJAX Endpoint: Tarik daftar model yang tersedia pada API Key tersebut
     */
    public function fetchAiModels() {
        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
            exit;
        }

        $apiKey   = $_POST['api_key'] ?? '';
        $apiUrl   = $_POST['api_url'] ?? '';
        $provider = $_POST['provider'] ?? 'nvidia';

        $aiModel = $this->model('AiSettingModel');
        $result  = $aiModel->fetchAvailableModels($apiKey, $apiUrl, $provider);

        echo json_encode($result);
        exit;
    }
}
