<?php
require_once 'c:/xampp/htdocs/universal/core/db.php';
$stmt = $pdo->query("SELECT id, parent_id, page_name, page_url FROM sys_pages WHERE page_url != '#'");
$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($pages, JSON_PRETTY_PRINT);
?>
