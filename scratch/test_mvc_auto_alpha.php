<?php
require_once 'c:/xampp/htdocs/sistemabsensi/config/config.php';
require_once 'c:/xampp/htdocs/sistemabsensi/database/Database.php';
require_once 'c:/xampp/htdocs/sistemabsensi/models/AbsensiSiswaModel.php';
require_once 'c:/xampp/htdocs/sistemabsensi/models/AbsensiGuruModel.php';
require_once 'c:/xampp/htdocs/sistemabsensi/models/AdminDashboardModel.php';

$siswaModel = new AbsensiSiswaModel();
$guruModel  = new AbsensiGuruModel();
$dashModel  = new AdminDashboardModel();

$tanggal = date('Y-m-d');
echo "Testing Models directly:\n";
$siswaAdded = $siswaModel->autoDataAlphaSiswa($tanggal);
$guruAdded  = $guruModel->autoDataAlphaGuru($tanggal);
echo "autoDataAlphaSiswa: $siswaAdded rows\n";
echo "autoDataAlphaGuru: $guruAdded rows\n";

$dashSiswa = $dashModel->getAbsensiSiswaHariIni($tanggal);
$dashGuru  = $dashModel->getAbsensiGuruHariIni($tanggal);

echo "Dashboard Siswa: Hadir={$dashSiswa['hadir']}, Alpa={$dashSiswa['alpa']}, Sakit={$dashSiswa['sakit']}, Izin={$dashSiswa['izin']}\n";
echo "Dashboard Guru: Hadir={$dashGuru['hadir']}, Tidak Hadir={$dashGuru['tidak_hadir']}, Total={$dashGuru['total']}\n";
