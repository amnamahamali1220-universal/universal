<?php
require_once 'core/db.php';
$stmt = $pdo->query('SELECT * FROM sys_roles');
while($row = $stmt->fetch()) {
    echo $row['role_key'] . ' ' . $row['role_name'] . PHP_EOL;
}
?>
