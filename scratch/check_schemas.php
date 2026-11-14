<?php
$pdo = new PDO('mysql:host=localhost;dbname=db_sistemabsensi', 'root', '');
echo "=== absensi_guru ===\n";
print_r($pdo->query('DESCRIBE absensi_guru')->fetchAll(PDO::FETCH_ASSOC));
echo "\n=== absensi_siswa ===\n";
print_r($pdo->query('DESCRIBE absensi_siswa')->fetchAll(PDO::FETCH_ASSOC));
