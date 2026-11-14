<?php
require_once '../config/config.php';
require_once '../app/core/App.php';
require_once '../app/core/Controller.php';
require_once '../app/core/Flasher.php';
require_once '../database/Database.php';
require_once '../app/core/Csrf.php';
require_once '../app/services/AiService.php';

// Global Security Helper for XSS Prevention
function e($string) {
    if (is_null($string)) {
        return '';
    }
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
