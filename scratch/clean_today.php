<?php
$pdo = new PDO('mysql:host=localhost;dbname=db_sistemabsensi', 'root', '');
$tanggal = date('Y-m-d');
$pdo->prepare("DELETE FROM absensi_siswa WHERE tanggal = :tgl")->execute(['tgl' => $tanggal]);
$pdo->prepare("DELETE FROM absensi_guru WHERE tanggal = :tgl")->execute(['tgl' => $tanggal]);
echo "Today ($tanggal) absensi test records cleared cleanly.\n";
