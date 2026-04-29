<?php
require_once '../core/db.php';
try {
    $stmt = $pdo->query("DESCRIBE enrollments");
    $cols = $stmt->fetchAll();
    echo "<h1>Table: enrollments</h1><table border='1'>";
    foreach ($cols as $col) {
        echo "<tr><td>{$col['Field']}</td><td>{$col['Type']}</td></tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
