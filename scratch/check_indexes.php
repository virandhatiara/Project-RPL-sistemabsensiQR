<?php
$pdo = new PDO('mysql:host=localhost;dbname=db_sistemabsensi', 'root', '');
echo "=== INDEXES absensi_siswa ===\n";
print_r($pdo->query('SHOW INDEX FROM absensi_siswa')->fetchAll(PDO::FETCH_ASSOC));
echo "\n=== INDEXES absensi_guru ===\n";
print_r($pdo->query('SHOW INDEX FROM absensi_guru')->fetchAll(PDO::FETCH_ASSOC));
