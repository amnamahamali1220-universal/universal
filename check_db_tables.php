<?php
require 'core/db.php';
$stmt = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo json_encode($tables);
foreach ($tables as $table) {
    echo "\nTable: " . $table . "\n";
    $stmt2 = $pdo->query("DESCRIBE " . $table);
    $cols = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $col) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}
?>
