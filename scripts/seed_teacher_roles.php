<?php
require_once 'core/db.php';

// Find some teachers
$stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'teacher' LIMIT 4");
$teachers = $stmt->fetchAll();

$roles = ['course_instructor', 'assignment_manager', 'exam_controller', 'senior_teacher'];

foreach ($teachers as $index => $t) {
    if (isset($roles[$index])) {
        $role = $roles[$index];
        $pdo->prepare("INSERT INTO teachers (user_id, teacher_role, department) VALUES (?, ?, ?) 
                       ON DUPLICATE KEY UPDATE teacher_role = ?")
            ->execute([$t['id'], $role, 'Computer Science', $role]);
        echo "Assigned $role to Teacher: {$t['name']}\n";
    }
}

if (empty($teachers)) {
    echo "No teachers found in users table. Please create some teachers first.\n";
}
?>
