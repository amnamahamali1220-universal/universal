<?php
session_start();
if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/core/db.php'; // Ensure db connection is active
    require_once __DIR__ . '/includes/activity_logger.php';
    logActivity($pdo, $_SESSION['user_id'], 'Logged out');
}
session_unset();
session_destroy();
header("Location: login.php");
exit;
?>