<?php
class AdminBarcodeController extends Controller {
    public function __construct() {
        parent::__construct();
        $this->checkAuth(['admin']);
    }

    public function index() {
        $type = isset($_GET['type']) && $_GET['type'] === 'guru' ? 'guru' : 'siswa';
        
        $data['judul'] = 'Data Barcode/QR ' . ucfirst($type);
        $data['type'] = $type;
        
        $limit = 10;
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $limit;
        
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
        
        if ($type === 'guru') {
            $model = $this->model('GuruModel');
            $data['items'] = $model->getAllGuru($limit, $offset, $keyword);
            $totalData = $model->countGuru($keyword);
        } else {
            $model = $this->model('SiswaModel');
            $data['items'] = $model->getAllSiswa($limit, $offset, $keyword);
            $totalData = $model->countSiswa($keyword);
        }
        
        $data['totalPages'] = ceil($totalData / $limit);
        $data['currentPage'] = $page;
        $data['keyword'] = $keyword;

        $this->view('templates/header', $data);
        $this->view('admin/barcode/index', $data);
        $this->view('templates/footer', $data);
    }

    public function assign($type, $id) {
        $type = $type === 'guru' ? 'guru' : 'siswa';
        $data['judul'] = 'Assign Barcode/QR ' . ucfirst($type);
        $data['type'] = $type;
        
        if ($type === 'guru') {
            $data['item'] = $this->model('GuruModel')->getGuruById($id);
        } else {
            $data['item'] = $this->model('SiswaModel')->getSiswaById($id);
        }
        
        if (!$data['item']) {
            $this->redirect('/admin-barcode?type=' . $type);
        }

        $this->view('templates/header', $data);
        $this->view('admin/barcode/assign', $data);
        $this->view('templates/footer', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            Csrf::validate();
            $type = isset($_POST['type']) && $_POST['type'] === 'guru' ? 'guru' : 'siswa';
            $barcode_id = trim($_POST['barcode_id']);
            $id = (int)$_POST['id'];
            
            $guruModel = $this->model('GuruModel');
            $siswaModel = $this->model('SiswaModel');

            if ($type === 'guru') {
                if (!empty($barcode_id)) {
                    if ($guruModel->getGuruByBarcode($barcode_id, $id)) {
                        Flasher::setFlash('Gagal!', 'Barcode ID sudah digunakan oleh guru lain.', 'danger');
                        $this->redirect('/admin-barcode/assign/guru/' . $id);
                    }
                    if ($siswaModel->getSiswaByBarcode($barcode_id)) {
                        Flasher::setFlash('Gagal!', 'Barcode ID sudah digunakan oleh seorang siswa.', 'danger');
                        $this->redirect('/admin-barcode/assign/guru/' . $id);
                    }
                }
                $currentItem = $guruModel->getGuruById($id);
                $currentItem['barcode_id'] = $barcode_id;
                $success = $guruModel->updateGuru($currentItem);
            } else {
                if (!empty($barcode_id)) {
                    if ($siswaModel->getSiswaByBarcode($barcode_id, $id)) {
                        Flasher::setFlash('Gagal!', 'Barcode ID sudah digunakan oleh siswa lain.', 'danger');
                        $this->redirect('/admin-barcode/assign/siswa/' . $id);
                    }
                    if ($guruModel->getGuruByBarcode($barcode_id)) {
                        Flasher::setFlash('Gagal!', 'Barcode ID sudah digunakan oleh seorang guru.', 'danger');
                        $this->redirect('/admin-barcode/assign/siswa/' . $id);
                    }
                }
                $currentItem = $siswaModel->getSiswaById($id);
                $currentItem['barcode_id'] = $barcode_id;
                $success = $siswaModel->updateSiswa($currentItem);
            }

            if ($success) {
                Flasher::setFlash('Berhasil!', 'Barcode ID berhasil diatur.', 'success');
                $this->redirect('/admin-barcode?type=' . $type);
            } else {
                Flasher::setFlash('Gagal!', 'Barcode ID gagal diatur.', 'danger');
                $this->redirect('/admin-barcode/assign/' . $type . '/' . $id);
            }
        }
    }

    public function generate($type, $id) {
        $type = $type === 'guru' ? 'guru' : 'siswa';
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') $this->redirect('/admin-barcode?type='.$type);
        Csrf::validate();

        $barcode_id = ($type === 'guru' ? 'QR-G-' : 'QR-S-') . time() . '-' . bin2hex(random_bytes(2));
        
        if ($type === 'guru') {
            $model = $this->model('GuruModel');
            $currentItem = $model->getGuruById($id);
            if ($currentItem) {
                $currentItem['barcode_id'] = $barcode_id;
                $model->updateGuru($currentItem);
                Flasher::setFlash('Berhasil!', 'Barcode Guru berhasil digenerate ulang.', 'success');
            }
        } else {
            $model = $this->model('SiswaModel');
            $currentItem = $model->getSiswaById($id);
            if ($currentItem) {
                $currentItem['barcode_id'] = $barcode_id;
                $model->updateSiswa($currentItem);
                Flasher::setFlash('Berhasil!', 'Barcode Siswa berhasil digenerate ulang.', 'success');
            }
        }
        $this->redirect('/admin-barcode?type='.$type);
    }

    public function print($type, $id) {
        $type = $type === 'guru' ? 'guru' : 'siswa';
        $data['judul'] = 'Cetak ID Card ' . ucfirst($type);
        $data['type'] = $type;

        if ($type === 'guru') {
            $data['items'] = [$this->model('GuruModel')->getGuruById($id)];
        } else {
            // Include class details
            $siswa = $this->model('SiswaModel')->getSiswaById($id);
            if ($siswa) {
                $kelas = $this->model('KelasModel')->getKelasById($siswa['kelas_id']);
                $siswa['nama_kelas'] = $kelas ? $kelas['nama_kelas'] : '-';
            }
            $data['items'] = [$siswa];
        }

        if (empty($data['items'][0]) || empty($data['items'][0]['barcode_id'])) {
            Flasher::setFlash('Gagal!', 'Data tidak ditemukan atau Barcode belum dibuat.', 'danger');
            $this->redirect('/admin-barcode?type='.$type);
        }

        $this->view('admin/barcode/print', $data);
    }

    public function printMassal($type) {
        $type = $type === 'guru' ? 'guru' : 'siswa';
        $data['judul'] = 'Cetak Massal ID Card ' . ucfirst($type);
        $data['type'] = $type;
        $keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

        if ($type === 'guru') {
            $data['items'] = $this->model('GuruModel')->getAllGuru(1000, 0, $keyword);
        } else {
            $items = $this->model('SiswaModel')->getAllSiswa(1000, 0, $keyword);
            foreach ($items as &$siswa) {
                $kelas = $this->model('KelasModel')->getKelasById($siswa['kelas_id']);
                $siswa['nama_kelas'] = $kelas ? $kelas['nama_kelas'] : '-';
            }
            $data['items'] = $items;
        }

        // Filter out items without barcode
        $data['items'] = array_filter($data['items'], function($item) {
            return !empty($item['barcode_id']);
        });

        if (empty($data['items'])) {
            Flasher::setFlash('Gagal!', 'Tidak ada data dengan Barcode yang siap dicetak.', 'danger');
            $this->redirect('/admin-barcode?type='.$type);
        }

        $this->view('admin/barcode/print', $data);
    }
}
