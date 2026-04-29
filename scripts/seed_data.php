<?php
require_once '../core/db.php';

echo "<h1>Seeding Initial Users...</h1>";

function createUser($pdo, $name, $email, $pass, $role, $reg = null, $identity = '123456') {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn()) {
        echo "User $email already exists.<br>";
        return;
    }

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, registration_no, identity_no, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
    $stmt->execute([$name, $email, $hash, $role, $reg, $identity]);
    echo "Created user: $name ($role)<br>";
}

try {
    // Admin
    createUser($pdo, 'Admin User', 'admin@example.com', 'admin123', 'admin');
    
    // Teacher
    createUser($pdo, 'John Teacher', 'teacher@example.com', 'teacher123', 'teacher');
    
    // Student
    createUser($pdo, 'Jane Student', 'student@example.com', 'student123', 'student', 'ST-2026-001');

    echo "<h3>Seeding Complete!</h3>";
    echo "<p>Login Credentials:</p>";
    echo "<ul>
        <li>Admin: admin@example.com / admin123</li>
        <li>Teacher: teacher@example.com / teacher123</li>
        <li>Student: student@example.com / student123</li>
    </ul>";

} catch (PDOException $e) {
    die("Seeding Error: " . $e->getMessage());
}
?>
