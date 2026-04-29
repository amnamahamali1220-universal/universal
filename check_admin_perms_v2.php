<?php
require_once 'core/db.php';
$stmt = $pdo->prepare("
    SELECT p.id, p.page_name, p.page_url 
    FROM sys_pages p
    JOIN role_access ra ON p.id = ra.page_id
    WHERE ra.role_key = 'admin'
");
$stmt->execute();
while ($row = $stmt->fetch()) {
    echo "ID: " . $row['id'] . " | Page: " . $row['page_name'] . " | URL: " . $row['page_url'] . "\n";
}
?>
