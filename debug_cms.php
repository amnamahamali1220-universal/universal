<?php
require_once 'core/db.php';

echo "<h1>Debug Info</h1>";

// 1. Check Roles
echo "<h2>Roles</h2>";
$roles = $pdo->query("SELECT * FROM sys_roles")->fetchAll();
foreach ($roles as $r) {
    echo "Role: {$r['role_key']} ({$r['role_name']})<br>";
}

// 2. Check Pages
echo "<h2>Pages</h2>";
$pages = $pdo->query("SELECT * FROM sys_pages ORDER BY id")->fetchAll();
foreach ($pages as $p) {
    echo "ID: {$p['id']} | Name: {$p['page_name']} | URL: {$p['page_url']} | Parent: {$p['parent_id']}<br>";
}

// 3. Check Permissions
echo "<h2>Permissions (Role Access)</h2>";
$perms = $pdo->query("SELECT * FROM role_access ORDER BY role_key")->fetchAll();
foreach ($perms as $p) {
    echo "Role: {$p['role_key']} -> Page ID: {$p['page_id']}<br>";
}

// 4. Check Demo Users
echo "<h2>Users</h2>";
$users = $pdo->query("SELECT id, name, email, role FROM users")->fetchAll();
foreach ($users as $u) {
    echo "User: {$u['name']} ({$u['email']}) - Role: {$u['role']}<br>";
}
?>
