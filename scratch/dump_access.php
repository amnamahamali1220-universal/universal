<?php
require_once '../core/db.php';

echo "--- sys_pages ---\n";
$stmt = $pdo->query("SELECT id, page_name, page_url FROM sys_pages");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Name: {$row['page_name']} | URL: {$row['page_url']}\n";
}

echo "\n--- role_access (Admin) ---\n";
$stmt = $pdo->query("SELECT ra.*, p.page_name FROM role_access ra JOIN sys_pages p ON ra.page_id = p.id WHERE ra.role_key = 'admin'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Page ID: {$row['page_id']} | Name: {$row['page_name']}\n";
}
?>
