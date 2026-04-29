<?php
require 'core/db.php';
$stmt = $pdo->query("SELECT * FROM sys_pages");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
