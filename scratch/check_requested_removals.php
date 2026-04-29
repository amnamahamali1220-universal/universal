<?php
require 'core/db.php';
$pages = [36, 37, 39, 40]; // Report Card, Global Settings, Submit Assignment, Rubric Builder
$sql = "SELECT ra.*, p.page_name 
        FROM role_access ra 
        JOIN sys_pages p ON ra.page_id = p.id 
        WHERE ra.page_id IN (" . implode(',', $pages) . ")";
$stmt = $pdo->query($sql);
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
