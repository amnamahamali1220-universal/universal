<?php
require 'core/db.php';

$forbidden = [36, 37, 39, 40]; // Report Card, Global Settings, Submit Assignment, Rubric Builder

try {
    // 1. Clear current super_admin access
    $pdo->prepare("DELETE FROM role_access WHERE role_key = 'super_admin'")->execute();
    
    // 2. Fetch all pages
    $all_pages = $pdo->query("SELECT id FROM sys_pages")->fetchAll(PDO::FETCH_COLUMN);
    
    // 3. Filter out forbidden ones
    $allowed = array_diff($all_pages, $forbidden);
    
    // 4. Grant access to super_admin
    $stmt = $pdo->prepare("INSERT INTO role_access (role_key, page_id) VALUES ('super_admin', ?)");
    foreach ($allowed as $pid) {
        $stmt->execute([$pid]);
    }
    
    // 5. Explicitly remove from admin just in case
    $pdo->prepare("DELETE FROM role_access WHERE role_key = 'admin' AND page_id IN (" . implode(',', $forbidden) . ")")->execute();

    echo "Role access updated for super_admin and admin.\n";
    echo "Removed pages: " . implode(', ', $forbidden) . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
