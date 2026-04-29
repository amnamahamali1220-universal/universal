<?php
require_once '../../core/session.php';
checkRole('admin');
require_once '../../core/db.php';

$user_id = $_GET['id'] ?? 0;

if ($user_id > 0) {
    // Get role to redirect back correctly
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $role = $stmt->fetchColumn();

    $delete = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $delete->execute([$user_id]);

    $redirect = ($role == 'student') ? 'students.php' : 'teachers.php';
    header("Location: $redirect?deleted=1");
    exit;
}

header("Location: index.php");
exit;
