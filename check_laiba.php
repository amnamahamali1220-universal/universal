<?php
require_once 'core/db.php';
$stmt = $pdo->prepare("SELECT * FROM users WHERE name LIKE ?");
$stmt->execute(['%Laiba%']);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "ID: " . $u['id'] . "\n";
    echo "Name: " . $u['name'] . "\n";
    echo "Email: [" . $u['email'] . "]\n";
    echo "Identity No: [" . $u['identity_no'] . "]\n";
    echo "Reg No: [" . $u['registration_no'] . "]\n";
    echo "Role: " . $u['role'] . "\n";
    echo "Active: " . $u['is_active'] . "\n";
    echo "Password Hash: " . $u['password'] . "\n";
    echo "is_hashed: " . (password_get_info($u['password'])['algo'] ? 'Y' : 'N') . "\n";
    echo "-------------------\n";
}
?>
