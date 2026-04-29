<?php
require_once __DIR__ . '/../core/db.php';

try {
    // 1. Add Teacher Quiz Page
    $stmt = $pdo->prepare("INSERT INTO sys_pages (parent_id, page_name, page_url, icon_class, sort_order) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([9, 'Manage Quizzes', 'dashboards/teacher/quizzes.php', 'bi bi-question-circle', 6]);
    $teacher_quiz_id = $pdo->lastInsertId();

    // 2. Add Student Quiz Page
    $stmt->execute([10, 'My Quizzes', 'dashboards/student/quizzes.php', 'bi bi-pencil-square', 5]);
    $student_quiz_id = $pdo->lastInsertId();

    // 3. Grant Access
    $access = $pdo->prepare("INSERT INTO role_access (role_key, page_id) VALUES (?, ?)");
    
    // Teacher access to Teacher Quiz
    $access->execute(['teacher', $teacher_quiz_id]);
    
    // Student access to Student Quiz
    $access->execute(['student', $student_quiz_id]);

    echo "Quiz menu items added successfully!\n";
    echo "Teacher Page ID: $teacher_quiz_id\n";
    echo "Student Page ID: $student_quiz_id\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
