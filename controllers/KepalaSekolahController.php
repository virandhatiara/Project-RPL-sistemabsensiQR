<?php
class KepalaSekolahController extends Controller {

    public function __construct() {
        parent::__construct();
        $this->checkAuth(['kepala_sekolah']);
    }

    public function dashboard() {
        $data['judul'] = 'Dashboard Kepala Sekolah';
        
        $ksModel = $this->model('KepalaSekolahModel');
        date_default_timezone_set('Asia/Jakarta');
        $tanggalHariIni = date('Y-m-d');
        
        // Otomatis masukkan siswa & guru yang belum absen sebagai Alpha
        $this->model('AbsensiSiswaModel')->autoDataAlphaSiswa($tanggalHariIni);
        $this->model('AbsensiGuruModel')->autoDataAlphaGuru($tanggalHariIni);

        $data['rekap'] = $ksModel->getRekapHariIni($tanggalHariIni);
        $data['tren'] = $ksModel->getTrenKehadiranSiswa();

        $this->view('templates/header', $data);
        $this->view('kepala_sekolah/dashboard', $data);
        $this->view('templates/footer', $data);
    }

    public function laporanSiswa() {
        $data['judul'] = 'Laporan Absensi Siswa';
        
        $kelasModel = $this->model('KelasModel');
        $data['daftar_kelas'] = $kelasModel->getAllKelas();

        $filters = [
            'tgl_awal' => isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01'),
            'tgl_akhir' => isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d'),
            'kelas_id' => isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '',
            'status' => isset($_GET['status']) ? $_GET['status'] : '',
            'nama' => isset($_GET['nama']) ? $_GET['nama'] : ''
        ];
        
        $data['filters'] = $filters;

        $ksModel = $this->model('KepalaSekolahModel');
        $data['laporan'] = $ksModel->getLaporanSiswa($filters);

        $this->view('templates/header', $data);
        $this->view('kepala_sekolah/laporan_siswa', $data);
        $this->view('templates/footer', $data);
    }

    public function laporanGuru() {
        $data['judul'] = 'Laporan Absensi Guru';

        $filters = [
            'tgl_awal' => isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : date('Y-m-01'),
            'tgl_akhir' => isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d'),
            'nama' => isset($_GET['nama']) ? $_GET['nama'] : ''
        ];
        
        $data['filters'] = $filters;

        $ksModel = $this->model('KepalaSekolahModel');
        $data['laporan'] = $ksModel->getLaporanGuru($filters);

        $this->view('templates/header', $data);
        $this->view('kepala_sekolah/laporan_guru', $data);
        $this->view('templates/footer', $data);
    }

    // Export Endpoint: CSV Siswa
    public function exportSiswaCsv() {
        $filters = [
            'tgl_awal' => isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : '',
            'tgl_akhir' => isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '',
            'kelas_id' => isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '',
            'status' => isset($_GET['status']) ? $_GET['status'] : '',
            'nama' => isset($_GET['nama']) ? $_GET['nama'] : ''
        ];

        $ksModel = $this->model('KepalaSekolahModel');
        $dataLaporan = $ksModel->getLaporanSiswa($filters);

        $filename = "Laporan_Absensi_Siswa_" . date('Ymd_His') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('No', 'NIS', 'Nama Siswa', 'Kelas', 'Tanggal', 'Jam', 'Status'));
        
        $no = 1;
        foreach ($dataLaporan as $row) {
            fputcsv($output, array(
                $no++,
                $row['nis'],
                $row['nama'],
                $row['nama_kelas'],
                $row['tanggal'],
                $row['jam'],
                ucfirst($row['status'])
            ));
        }
        fclose($output);
        exit;
    }

    // Export Endpoint: CSV Guru
    public function exportGuruCsv() {
        $filters = [
            'tgl_awal' => isset($_GET['tgl_awal']) ? $_GET['tgl_awal'] : '',
            'tgl_akhir' => isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : '',
            'nama' => isset($_GET['nama']) ? $_GET['nama'] : ''
        ];

        $ksModel = $this->model('KepalaSekolahModel');
        $dataLaporan = $ksModel->getLaporanGuru($filters);

        $filename = "Laporan_Absensi_Guru_" . date('Ymd_His') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('No', 'NIP', 'Nama Guru', 'Tanggal', 'Jam Masuk', 'Jam Pulang'));
        
        $no = 1;
        foreach ($dataLaporan as $row) {
            fputcsv($output, array(
                $no++,
                $row['nip'],
                $row['nama'],
                $row['tanggal'],
                $row['jam_masuk'] ? $row['jam_masuk'] : '-',
                $row['jam_pulang'] ? $row['jam_pulang'] : '-'
            ));
        }
        fclose($output);
        exit;
    }
}
