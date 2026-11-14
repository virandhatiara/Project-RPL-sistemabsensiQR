<?php
class Csrf {
    // Generate token and store it in session
    public static function getToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Validate the token from POST against Session
    public static function validate() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
                // CSRF Validation Failed
                http_response_code(403);
                die('403 Forbidden - CSRF Token Validation Failed. Please refresh the page and try again.');
            }
        }
    }
}
