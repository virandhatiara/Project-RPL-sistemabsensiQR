<?php
try {
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SHOW DATABASES");
    $dbs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Connected successfully. Databases:\n";
    print_r($dbs);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
