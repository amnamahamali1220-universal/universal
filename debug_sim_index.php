<?php
require_once 'c:/xampp/htdocs/universal/core/db.php';
require_once 'c:/xampp/htdocs/universal/core/session.php';

// Simulate root admin session
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'super_admin';
$_SESSION['name'] = 'Root Admin';

// We'll capture the output of index.php if possible
ob_start();
try {
    // Override SCRIPT_NAME to simulate accessing index.php from web
    $_SERVER['SCRIPT_NAME'] = '/universal/index.php';
    require 'c:/xampp/htdocs/universal/index.php';
    $output = ob_get_clean();
    echo "SUCCESS\n";
    // echo $output; // Un-comment to see output, but we just want to know if it dies
} catch (Throwable $e) {
    echo "CAUGHT ERROR: " . $e->getMessage() . "\n";
}
?>
