<?php
require_once 'core/db.php';
require_once 'core/session.php';

echo "<h1>System Access Debug</h1>";
echo "Current Role: " . ($_SESSION['role'] ?? 'NOT LOGGED IN') . "<br>";
echo "Current SCRIPT_NAME: " . $_SERVER['SCRIPT_NAME'] . "<br>";

$current_url = substr($_SERVER['SCRIPT_NAME'], strlen('/universal/'));
echo "Calculated relative URL: $current_url<br>";

// 1. Inspect sys_pages
echo "<h2>System Pages</h2>";
$pages = $pdo->query("SELECT * FROM sys_pages ORDER BY id ASC")->fetchAll();
echo "<table border='1'><tr><th>ID</th><th>Parent</th><th>Name</th><th>URL</th><th>Icon</th></tr>";
foreach ($pages as $p) {
    echo "<tr><td>{$p['id']}</td><td>{$p['parent_id']}</td><td>{$p['page_name']}</td><td>{$p['page_url']}</td><td>{$p['icon_class']}</td></tr>";
}
echo "</table>";

// 2. Inspect role_access
echo "<h2>Role Access</h2>";
$access = $pdo->query("SELECT ra.*, p.page_name, p.page_url FROM role_access ra JOIN sys_pages p ON ra.page_id = p.id ORDER BY ra.role_key ASC")->fetchAll();
echo "<table border='1'><tr><th>Role</th><th>Page ID</th><th>Page Name</th><th>Page URL</th></tr>";
foreach ($access as $a) {
    echo "<tr><td>{$a['role_key']}</td><td>{$a['page_id']}</td><td>{$a['page_name']}</td><td>{$a['page_url']}</td></tr>";
}
echo "</table>";

// 3. Test Matching Logic from header.php
echo "<h2>Test Matching</h2>";
$pageStmt = $pdo->prepare("SELECT * FROM sys_pages WHERE page_url LIKE ? LIMIT 1");
$pageStmt->execute(["%$current_url%"]); 
$match = $pageStmt->fetch();

if ($match) {
    echo "Matched Page ID: " . $match['id'] . " (" . $match['page_name'] . ")<br>";
    if ($_SESSION['role'] !== 'super_admin') {
        $accessStmt = $pdo->prepare("SELECT * FROM role_access WHERE role_key = ? AND page_id = ?");
        $accessStmt->execute([$_SESSION['role'], $match['id']]);
        if ($accessStmt->rowCount() == 0) {
            echo "<b style='color:red'>ACCESS WOULD BE DENIED</b>";
        } else {
            echo "<b style='color:green'>ACCESS GRANTED</b>";
        }
    } else {
        echo "<b style='color:green'>SUPER ADMIN BYPASS</b>";
    }
} else {
    echo "No Match Found in sys_pages (Security Bypass - checkRole still applies)";
}
?>
