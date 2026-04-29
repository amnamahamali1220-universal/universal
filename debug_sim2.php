<?php
require_once 'c:/xampp/htdocs/universal/core/db.php';

// Setup Mock Session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'super_admin';
$_SESSION['name'] = 'Root Admin';

// We want to see what file is killing the process with "Access Denied or Course Not Found"
register_shutdown_function(function() {
    $error = error_get_last();
    echo "\nSHUTDOWN OCCURRED!\n";
    if ($error !== null) {
        print_r($error);
    }
    
    // Let's print the backtrace of included files
    echo "INCLUDED FILES:\n";
    print_r(get_included_files());
});

$_SERVER['SCRIPT_NAME'] = '/universal/index.php';
$_SERVER['REQUEST_URI'] = '/universal/';
$_SERVER['REQUEST_METHOD'] = 'GET';

ob_start();
require 'c:/xampp/htdocs/universal/index.php';
$output = ob_get_clean();

echo "\nFINISHED NORMALLY. Output length: " . strlen($output) . "\n";
if (strpos($output, 'Course Not Found') !== false) {
    echo "ERROR STRING FOUND IN NORMALLY FINISHED OUTPUT!\n";
}
?>
