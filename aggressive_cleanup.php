<?php
require_once 'core/db.php';
$stmt = $pdo->prepare("
    DELETE FROM role_access 
    WHERE role_key LIKE 'admin%' 
    AND page_id IN (
        SELECT id FROM sys_pages 
        WHERE page_name IN ('Global Settings', 'Submit Assignment', 'Report Card', 'Rubric Builder')
    )
");
$stmt->execute();
echo "Deleted " . $stmt->rowCount() . " rows\n";

// Also check for 'Administrator' role just in case
$stmt = $pdo->prepare("
    DELETE FROM role_access 
    WHERE role_key = 'Administrator' 
    AND page_id IN (
        SELECT id FROM sys_pages 
        WHERE page_name IN ('Global Settings', 'Submit Assignment', 'Report Card', 'Rubric Builder')
    )
");
$stmt->execute();
echo "Deleted (Administrator) " . $stmt->rowCount() . " rows\n";
?>
