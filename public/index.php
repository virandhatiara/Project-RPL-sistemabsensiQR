<?php
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on', // Only on HTTPS
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
require_once '../app/init.php';
$app = new App();
