<?php
$pdo = new PDO('mysql:host=localhost;dbname=db_sistemabsensi', 'root', '');

$tanggal = date('Y-m-d');
echo "Testing auto alpha for $tanggal\n";

// 1. Check current counts
$stmt = $pdo->query("SELECT COUNT(*) as total FROM siswa");
$totalSiswa = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM guru");
$totalGuru = $stmt->fetch()['total'];

echo "Total siswa di DB: $totalSiswa\n";
echo "Total guru di DB: $totalGuru\n";

// 2. Count before auto alpha
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM absensi_siswa WHERE tanggal = :tgl");
$stmt->execute(['tgl' => $tanggal]);
echo "Absensi siswa hari ini sebelum: " . $stmt->fetch()['total'] . "\n";

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM absensi_guru WHERE tanggal = :tgl");
$stmt->execute(['tgl' => $tanggal]);
echo "Absensi guru hari ini sebelum: " . $stmt->fetch()['total'] . "\n";

// 3. Run auto alpha query for siswa
$stmt = $pdo->prepare("
    INSERT INTO absensi_siswa (siswa_id, tanggal, jam, status)
    SELECT s.id, :tanggal, '07:00:00', 3
    FROM siswa s
    WHERE s.id NOT IN (
        SELECT a.siswa_id FROM absensi_siswa a WHERE a.tanggal = :tanggal_cek
    )
");
$stmt->execute(['tanggal' => $tanggal, 'tanggal_cek' => $tanggal]);
$insertedSiswa = $stmt->rowCount();
echo "Inserted siswa alpha: $insertedSiswa\n";

// 4. Run auto alpha query for guru
$stmt = $pdo->prepare("
    INSERT INTO absensi_guru (guru_id, tanggal, jam_masuk, status)
    SELECT g.id, :tanggal, '07:00:00', 'alpa'
    FROM guru g
    WHERE g.id NOT IN (
        SELECT ag.guru_id FROM absensi_guru ag WHERE ag.tanggal = :tanggal_cek
    )
");
$stmt->execute(['tanggal' => $tanggal, 'tanggal_cek' => $tanggal]);
$insertedGuru = $stmt->rowCount();
echo "Inserted guru alpha: $insertedGuru\n";

// 5. Test idempotency (run again)
$stmt->execute(['tanggal' => $tanggal, 'tanggal_cek' => $tanggal]);
echo "Run again guru alpha: " . $stmt->rowCount() . " (Should be 0)\n";

// 6. Check summary status
echo "\nStatus absensi siswa hari ini:\n";
$stmt = $pdo->prepare("SELECT status, COUNT(*) as jml FROM absensi_siswa WHERE tanggal = :tgl GROUP BY status");
$stmt->execute(['tgl' => $tanggal]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\nStatus absensi guru hari ini:\n";
$stmt = $pdo->prepare("SELECT status, COUNT(*) as jml FROM absensi_guru WHERE tanggal = :tgl GROUP BY status");
$stmt->execute(['tgl' => $tanggal]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

// Clean up today's test records
$pdo->prepare("DELETE FROM absensi_siswa WHERE tanggal = :tgl AND status = 3")->execute(['tgl' => $tanggal]);
$pdo->prepare("DELETE FROM absensi_guru WHERE tanggal = :tgl AND status = 'alpa'")->execute(['tgl' => $tanggal]);
echo "\nCleaned up test alpha records.\n";
