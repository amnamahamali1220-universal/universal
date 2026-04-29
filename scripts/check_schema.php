<?php
require_once 'core/db.php';
$stmt = $pdo->query("SHOW TABLES");
while($row = $stmt->fetch(PDO::FETCH_NUM)) {
    $table = $row[0];
    echo "Table: $table\n";
    $cols = $pdo->query("DESCRIBE $table");
    while($col = $cols->fetch()) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
}
?>
