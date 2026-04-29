<?php
require_once 'core/db.php';
$output = "ROLES:\n";
$stmt = $pdo->query("SELECT * FROM sys_roles");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $output .= json_encode($row) . "\n";
}
$output .= "\nUSERS:\n";
$stmt = $pdo->query("SELECT id, name, email, role, identity_no, registration_no, is_active FROM users");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $output .= json_encode($row) . "\n";
}
file_put_contents('db_dump.txt', $output);
?>
