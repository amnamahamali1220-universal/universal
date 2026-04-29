<?php
require_once '../core/db.php';

echo "<h1>Fixing DB Paths</h1>";

// 1. Fix Absolute Paths causing 403 Forbidden
// Problem: Some paths are "C:\xampp\htdocs\universal\dashboards\student" instead of "#" or relative path
// Fix: 
// - Student -> #
// - Teacher -> #
// - Super Admin -> #
// (Since they are headers for submenus)

$updates = [
    'Student' => '#',
    'Teacher' => '#',
    'Super Admin' => '#', 
    'Administration' => '#',
    'System Management' => '#'
];

try {
    foreach ($updates as $name => $url) {
        $stmt = $pdo->prepare("UPDATE sys_pages SET page_url = ? WHERE page_name = ?");
        $stmt->execute([$url, $name]);
        if ($stmt->rowCount() > 0) {
            echo "Updated '$name' to '$url'<br>";
        } else {
            echo "No update needed for '$name' (or not found)<br>";
        }
    }

    // 2. Fix Slashing issues if any (Windows backslashes to forward slashes)
    $stmt = $pdo->query("SELECT id, page_url FROM sys_pages");
    while ($row = $stmt->fetch()) {
        if (strpos($row['page_url'], '\\') !== false) {
            $newUrl = str_replace('\\', '/', $row['page_url']);
            // Also remove C:/xampp/htdocs/universal/ prefix if present
            $newUrl = str_replace('C:/xampp/htdocs/universal/', '', $newUrl);
            
            $update = $pdo->prepare("UPDATE sys_pages SET page_url = ? WHERE id = ?");
            $update->execute([$newUrl, $row['id']]);
            echo "Fixed slash/path for ID {$row['id']}: $newUrl<br>";
        }
    }

    echo "<h3>Fix Complete</h3>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
