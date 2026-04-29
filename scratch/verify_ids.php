<?php
require 'core/db.php';
$stmt = $pdo->query("SELECT id, page_name, page_url FROM sys_pages WHERE page_name IN ('Global Settings', 'Submit Assignment', 'Rubric Builder', 'Report Card', 'Global Setting', 'Submit Assigment', 'Rubric builder', 'report card')");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
