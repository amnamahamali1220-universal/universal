<?php
session_start();
require_once 'core/db.php';
require_once 'core/teacher_roles.php';

// Mock session for a teacher
function testRole($userId, $expectedSubRole) {
    global $pdo;
    $_SESSION['user_id'] = $userId;
    $_SESSION['role'] = 'teacher';
    
    echo "Testing User ID $userId (Expected: $expectedSubRole)...\n";
    syncTeacherRole();
    
    echo "Current Session Role: " . $_SESSION['role'] . "\n";
    if ($_SESSION['role'] === $expectedSubRole) {
        echo "[PASS] Role synced correctly.\n";
    } else {
        echo "[FAIL] Role sync mismatch.\n";
    }
    echo "-------------------\n";
}

// Find the teachers we seeded
$stmt = $pdo->query("SELECT u.id, t.teacher_role FROM users u JOIN teachers t ON u.id = t.user_id LIMIT 4");
$rows = $stmt->fetchAll();

foreach ($rows as $row) {
    testRole($row['id'], $row['teacher_role']);
}
?>
