<?php
require_once 'config/config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$result = $conn->query("SHOW TABLES");
while($row = $result->fetch_array()) {
    $table = $row[0];
    echo "TABLE: $table\n";
    $columns = $conn->query("SHOW COLUMNS FROM $table");
    while($col = $columns->fetch_assoc()) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
    echo "\n";
}
$conn->close();
?>
