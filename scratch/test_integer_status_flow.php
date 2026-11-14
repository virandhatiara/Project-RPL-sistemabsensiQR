<?php
// Test verification script for integer status attendance
$host = 'localhost';
$db   = 'db_sistemabsensi';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

echo "=== 1. VERIFIKASI STRUKTUR TABEL absensi_siswa ===\n";
$stmt = $pdo->query("DESCRIBE absensi_siswa status");
$col = $stmt->fetch();
echo "Column: " . $col['Field'] . "\n";
echo "Type: " . $col['Type'] . "\n";
echo "Default: " . $col['Default'] . "\n";

echo "\n=== 2. AMBIL 1 SISWA TEST ===\n";
$stmt = $pdo->query("SELECT id, nis, nama, barcode_id FROM siswa LIMIT 1");
$siswa = $stmt->fetch();
echo "Testing with Siswa ID {$siswa['id']} | NIS: {$siswa['nis']} | Nama: {$siswa['nama']} | Barcode: {$siswa['barcode_id']}\n";

$today = date('Y-m-d');
// Bersihkan absensi hari ini untuk testing
$pdo->prepare("DELETE FROM absensi_siswa WHERE siswa_id = :sid AND tanggal = :tgl")
    ->execute(['sid' => $siswa['id'], 'tgl' => $today]);
echo "Cleared existing attendance for today.\n";

echo "\n=== 3. TEST POST KE proses_absen.php DENGAN STATUS ANGKA (1 = SAKIT) ===\n";
$url = "http://localhost/sistemabsensi/public/proses_absen.php";
$postFields = [
    'barcode_id' => $siswa['barcode_id'],
    'status'     => 1 // Angka integer 1 = Sakit
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";

$json = json_decode($response, true);
if ($json && $json['status'] === 'success') {
    echo "SUCCESS: proses_absen.php accepted numeric status!\n";
} else {
    echo "FAILED or WARNING: " . ($json['message'] ?? 'Unknown error') . "\n";
}

echo "\n=== 4. CEK NILAI STATUS DI DATABASE MYSQL ===\n";
$stmt = $pdo->prepare("SELECT * FROM absensi_siswa WHERE siswa_id = :sid AND tanggal = :tgl");
$stmt->execute(['sid' => $siswa['id'], 'tgl' => $today]);
$row = $stmt->fetch();
if ($row) {
    echo "Stored row: ID={$row['id']}, Tanggal={$row['tanggal']}, Jam={$row['jam']}, Status={$row['status']} (Type: " . gettype($row['status']) . ")\n";
    if ((int)$row['status'] === 1) {
        echo "VERIFIED: Database strictly stored TINYINT 1 (Sakit)!\n";
    } else {
        echo "MISMATCH: Expected status 1, got {$row['status']}\n";
    }
} else {
    echo "ERROR: Row not found in database!\n";
}

echo "\n=== 5. TEST RE-SCAN MENCEGAH LOOPING DENGAN KETERANGAN STATUS LAMA ===\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res2 = curl_exec($ch);
curl_close($ch);
echo "Duplicate Scan Response:\n$res2\n";

echo "\n=== 6. CLEAN UP TEST RECORD ===\n";
$pdo->prepare("DELETE FROM absensi_siswa WHERE siswa_id = :sid AND tanggal = :tgl")
    ->execute(['sid' => $siswa['id'], 'tgl' => $today]);
echo "Test attendance record cleaned up successfully.\n";
