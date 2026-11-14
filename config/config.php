<?php
// Load environment variables from .env file if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Helper to get env variable
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        return $default;
    }
    return $value;
}

define('BASEURL', env('BASEURL', 'http://localhost/sistemabsensi/public'));

// DB Constants
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_NAME', env('DB_NAME', 'db_sistemabsensi'));

// AI Config
define('AI_API_KEY', env('AI_API_KEY', ''));

// Telegram Bot Config (@AbsensiSmkNuBot)
if (!defined('TELEGRAM_BOT_TOKEN')) {
    define('TELEGRAM_BOT_TOKEN', env('TELEGRAM_BOT_TOKEN', ''));
}
if (!defined('TELEGRAM_CHAT_ID')) {
    define('TELEGRAM_CHAT_ID', env('TELEGRAM_CHAT_ID', ''));
}

// Fonnte WhatsApp API Config
if (!defined('FONNTE_TOKEN')) {
    define('FONNTE_TOKEN', env('FONNTE_TOKEN', ''));
}
