<?php
require_once 'core/db.php';
session_start();

echo "<h1>Database User Check</h1>";

try {
    $stmt = $pdo->query("SELECT id, name, email, role, is_active FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1'><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Active</th></tr>";
    foreach ($users as $u) {
        echo "<tr>";
        foreach ($u as $k => $v) echo "<td>$v</td>";
        echo "</tr>";
    }
    echo "</table>";

    echo "<h2>Session Status</h2>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
