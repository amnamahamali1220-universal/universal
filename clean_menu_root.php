<?php
require_once 'core/db.php';

echo "<h1>Cleaning Menu Items</h1>";

// IDs to delete
$idsToDelete = [11, 12, 13, 14, 15];

try {
    $placeholders = implode(',', array_fill(0, count($idsToDelete), '?'));

    // 1. Delete from role_access first (Foreign Key checks might cascade, but let's be safe)
    $stmt = $pdo->prepare("DELETE FROM role_access WHERE page_id IN ($placeholders)");
    $stmt->execute($idsToDelete);
    echo "Deleted role_access entries for IDs: " . implode(', ', $idsToDelete) . "<br>";

    // 2. Delete from sys_pages
    $stmt = $pdo->prepare("DELETE FROM sys_pages WHERE id IN ($placeholders)");
    $stmt->execute($idsToDelete);
    echo "Deleted sys_pages entries for IDs: " . implode(', ', $idsToDelete) . "<br>";

    echo "<h3>Cleanup Complete!</h3>";
    echo "<p>Please refresh your dashboard to see the changes.</p>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
