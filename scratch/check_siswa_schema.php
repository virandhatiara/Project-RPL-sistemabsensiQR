<?php
require_once __DIR__ . '/../config/config.php';
$pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
print_r($pdo->query("SHOW COLUMNS FROM siswa")->fetchAll(PDO::FETCH_ASSOC));
