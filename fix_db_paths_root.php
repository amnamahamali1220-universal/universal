<?php
require_once 'core/db.php';

echo "<h1>Fixing DB Paths</h1>";

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

    // Fix Slashing issues
    $stmt = $pdo->query("SELECT id, page_url FROM sys_pages");
    while ($row = $stmt->fetch()) {
        if (strpos($row['page_url'], '\\') !== false || strpos($row['page_url'], 'C:/') !== false) {
            $newUrl = str_replace('\\', '/', $row['page_url']);
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
