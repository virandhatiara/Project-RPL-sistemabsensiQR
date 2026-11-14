<?php
require_once __DIR__ . '/../config/config.php';
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=smk_nu_warga", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SHOW TABLES LIKE 'chat_history'");
    if($stmt->rowCount() > 0) {
        echo "Table exists!";
    } else {
        echo "Table DOES NOT exist. Creating it...";
        $query = "CREATE TABLE IF NOT EXISTS `chat_history` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `message_user` text NOT NULL,
          `message_ai` text NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `user_id` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $pdo->exec($query);
        echo " Created.";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
