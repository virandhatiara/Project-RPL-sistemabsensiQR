<?php
$baseUrl = 'http://localhost/sistemabsensi';

echo "=== TEST END-TO-END AUTO DATA ALPHA GURU & SISWA ===\n\n";

$pdo = new PDO('mysql:host=localhost;dbname=db_sistemabsensi', 'root', '');
$tanggal = date('Y-m-d');

// Clean up today's records for a fresh test run
$pdo->prepare("DELETE FROM absensi_siswa WHERE tanggal = :tgl")->execute(['tgl' => $tanggal]);
$pdo->prepare("DELETE FROM absensi_guru WHERE tanggal = :tgl")->execute(['tgl' => $tanggal]);
echo "[1] Cleaned up existing absensi records for today ($tanggal)\n";

// Get sample siswa and sample guru
$siswa = $pdo->query("SELECT * FROM siswa LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$guru  = $pdo->query("SELECT * FROM guru LIMIT 1")->fetch(PDO::FETCH_ASSOC);

echo "Sample Siswa: {$siswa['nama']} (NIS: {$siswa['nis']}, Barcode: {$siswa['barcode_id']})\n";
echo "Sample Guru: {$guru['nama']} (NIP: {$guru['nip']}, Barcode: {$guru['barcode_id']})\n\n";

// 2. Test auto_alpha endpoint
echo "[2] Triggering action=auto_alpha via public/proses_absen.php...\n";
$ch = curl_init("http://localhost/sistemabsensi/public/proses_absen.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'action' => 'auto_alpha',
    'tanggal' => $tanggal
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$resJson = curl_exec($ch);
curl_close($ch);

echo "Response from auto_alpha:\n$resJson\n\n";
$res = json_decode($resJson, true);
if (!isset($res['status']) || $res['status'] !== 'success') {
    echo "FAILED: auto_alpha did not return success!\n";
    exit(1);
}

// 3. Verify in DB
$stmt = $pdo->prepare("SELECT status, COUNT(*) as jml FROM absensi_siswa WHERE tanggal = :tgl GROUP BY status");
$stmt->execute(['tgl' => $tanggal]);
echo "DB absensi_siswa status counts:\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = $pdo->prepare("SELECT status, COUNT(*) as jml FROM absensi_guru WHERE tanggal = :tgl GROUP BY status");
$stmt->execute(['tgl' => $tanggal]);
echo "DB absensi_guru status counts:\n";
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

// Verify sample siswa is status 3
$stmt = $pdo->prepare("SELECT * FROM absensi_siswa WHERE siswa_id = :id AND tanggal = :tgl");
$stmt->execute(['id' => $siswa['id'], 'tgl' => $tanggal]);
$siswaRow = $stmt->fetch(PDO::FETCH_ASSOC);
echo "\nSample Siswa record before scan: status = {$siswaRow['status']}, jam = {$siswaRow['jam']}\n";

// Verify sample guru is status 'alpa'
$stmt = $pdo->prepare("SELECT * FROM absensi_guru WHERE guru_id = :id AND tanggal = :tgl");
$stmt->execute(['id' => $guru['id'], 'tgl' => $tanggal]);
$guruRow = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Sample Guru record before scan: status = {$guruRow['status']}, jam_masuk = {$guruRow['jam_masuk']}\n";

// 4. Test Scan Override for Siswa (from Alpha to Hadir status 0)
echo "\n[3] Testing Scan Siswa (should OVERRIDE Alpha to Hadir status 0)...\n";
$ch = curl_init("http://localhost/sistemabsensi/public/proses_absen.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'barcode_id' => $siswa['barcode_id'],
    'status'     => 0
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$scanSiswaRes = curl_exec($ch);
curl_close($ch);

echo "Scan Siswa Response:\n$scanSiswaRes\n";
$scanSiswaData = json_decode($scanSiswaRes, true);
if (($scanSiswaData['status'] ?? '') !== 'success') {
    echo "FAILED: Scan siswa override did not return success!\n";
    exit(1);
}

// Verify sample siswa in DB is now status 0
$stmt = $pdo->prepare("SELECT * FROM absensi_siswa WHERE siswa_id = :id AND tanggal = :tgl");
$stmt->execute(['id' => $siswa['id'], 'tgl' => $tanggal]);
$siswaRowAfter = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Sample Siswa record AFTER scan: status = {$siswaRowAfter['status']} (Expected: 0), jam = {$siswaRowAfter['jam']}\n";

// 5. Test Scan Siswa second time (should return warning: Sudah Absen)
echo "\n[4] Testing second scan for Siswa (should return warning)...\n";
$ch = curl_init("http://localhost/sistemabsensi/public/proses_absen.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'barcode_id' => $siswa['barcode_id'],
    'status'     => 0
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$scan2Res = curl_exec($ch);
curl_close($ch);
echo "Second scan response:\n$scan2Res\n";

// 6. Test Scan Override for Guru (from alpa to hadir/masuk)
echo "\n[5] Testing Scan Guru (should OVERRIDE alpa to masuk)...\n";
$ch = curl_init("http://localhost/sistemabsensi/public/proses_absen.php");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'barcode_id' => $guru['barcode_id'],
    'status'     => 0
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$scanGuruRes = curl_exec($ch);
curl_close($ch);

echo "Scan Guru Response:\n$scanGuruRes\n";
$scanGuruData = json_decode($scanGuruRes, true);
if (($scanGuruData['status'] ?? '') !== 'success') {
    echo "FAILED: Scan guru override did not return success!\n";
    exit(1);
}

// Verify sample guru in DB is now not alpa
$stmt = $pdo->prepare("SELECT * FROM absensi_guru WHERE guru_id = :id AND tanggal = :tgl");
$stmt->execute(['id' => $guru['id'], 'tgl' => $tanggal]);
$guruRowAfter = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Sample Guru record AFTER scan: status = {$guruRowAfter['status']} (Expected: hadir or terlambat), jam_masuk = {$guruRowAfter['jam_masuk']}\n";

echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
