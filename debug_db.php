<?php
require_once 'core/db.php';

echo "<h1>Database Debug</h1>";

function dumpTable($pdo, $table) {
    echo "<h2>$table</h2>";
    $stmt = $pdo->query("SELECT * FROM $table");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "Empty table.";
        return;
    }
    echo "<table border='1'><tr>";
    foreach (array_keys($rows[0]) as $k) echo "<th>$k</th>";
    echo "</tr>";
    foreach ($rows as $r) {
        echo "<tr>";
        foreach ($r as $v) echo "<td>$v</td>";
        echo "</tr>";
    }
    echo "</table>";
}

try {
    dumpTable($pdo, 'sys_roles');
    dumpTable($pdo, 'sys_pages');
    dumpTable($pdo, 'role_access');
    dumpTable($pdo, 'users');

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
