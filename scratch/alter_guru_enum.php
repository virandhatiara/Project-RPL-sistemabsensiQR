<?php
$pdo = new PDO('mysql:host=localhost;dbname=db_sistemabsensi', 'root', '');
$pdo->exec("ALTER TABLE absensi_guru MODIFY COLUMN status ENUM('hadir', 'terlambat', 'sakit', 'izin', 'alpa') NOT NULL DEFAULT 'hadir'");
echo "ENUM altered successfully\n";
print_r($pdo->query("DESCRIBE absensi_guru")->fetchAll(PDO::FETCH_ASSOC));
