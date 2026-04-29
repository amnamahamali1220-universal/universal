<?php
require_once 'c:/xampp/htdocs/universal/core/db.php';
$stmt = $pdo->query("SELECT * FROM sys_pages ORDER BY sort_order ASC");
$pages = $stmt->fetchAll();
echo json_encode($pages, JSON_PRETTY_PRINT);
?>
