<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../models/AbsensiSiswaModel.php';

$db = new Database();
$absensiModel = new AbsensiSiswaModel();

echo "=== 1. TEST QUERY FILTER AbsensiSiswaModel DENGAN STATUS ANGKA 0, 1, 2, 3 ===\n";
// Insert 4 test records with each integer status
$today = date('Y-m-d');
$db->query("DELETE FROM absensi_siswa WHERE tanggal = :tgl");
$db->bind('tgl', $today);
$db->execute();

$statuses = [
    0 => 'Hadir',
    1 => 'Sakit',
    2 => 'Izin',
    3 => 'Alpha'
];

// Get 4 student IDs
$db->query("SELECT id FROM siswa ORDER BY id ASC LIMIT 4");
$siswaList = $db->resultSet();

foreach ($siswaList as $idx => $s) {
    $stVal = $idx;
    $absensiModel->tambahAbsensi([
        'siswa_id' => $s['id'],
        'tanggal'  => $today,
        'jam'      => sprintf('07:%02d:00', $idx * 5),
        'status'   => $stVal
    ]);
    echo "Inserted Siswa ID {$s['id']} with status integer {$stVal} ({$statuses[$stVal]})\n";
}

// Test stats aggregation
$stats = $absensiModel->getStatistikKehadiran($today);
echo "\nStatistik Kehadiran:\n";
echo "- Total: {$stats['total']}\n";
echo "- Hadir: {$stats['total_hadir']}\n";
echo "- Sakit: {$stats['sakit']}\n";
echo "- Izin:  {$stats['izin']}\n";
echo "- Alpha: {$stats['alpa']}\n";

// Test filter for status 0 (Hadir)
$resHadir = $absensiModel->getAbsensiFiltered($today, null, 0);
echo "\nFilter Status = 0 (Hadir): found " . count($resHadir) . " record(s)\n";

// Test filter for status 1 (Sakit)
$resSakit = $absensiModel->getAbsensiFiltered($today, null, 1);
echo "Filter Status = 1 (Sakit): found " . count($resSakit) . " record(s)\n";

// Test filter for status 2 (Izin)
$resIzin = $absensiModel->getAbsensiFiltered($today, null, 2);
echo "Filter Status = 2 (Izin):  found " . count($resIzin) . " record(s)\n";

// Test filter for status 3 (Alpha)
$resAlpha = $absensiModel->getAbsensiFiltered($today, null, 3);
echo "Filter Status = 3 (Alpha): found " . count($resAlpha) . " record(s)\n";

// Clean up
$db->query("DELETE FROM absensi_siswa WHERE tanggal = :tgl");
$db->bind('tgl', $today);
$db->execute();
echo "\nCleaned up test records successfully.\n";
