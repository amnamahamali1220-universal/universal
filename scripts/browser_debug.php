<?php
require_once '../core/db.php';
echo "<pre>";
echo "DB_NAME: " . DB_NAME . "\n";
$stmt = $pdo->query("SELECT DATABASE()");
echo "Current Database: " . $stmt->fetchColumn() . "\n";
echo "Tables:\n";
$stmt = $pdo->query("SHOW TABLES");
print_r($stmt->fetchAll(PDO::FETCH_COLUMN));
echo "</pre>";
?>
