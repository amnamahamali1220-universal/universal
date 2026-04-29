<?php
require_once '../core/db.php';
try {
    $student_id = 1; // dummy
    $stmt = $pdo->prepare("
        SELECT c.*, t.name as teacher_name 
        FROM enrollments e 
        JOIN courses c ON e.course_id = c.id 
        JOIN users t ON c.teacher_id = t.id 
        WHERE e.student_id = ?
    ");
    $stmt->execute([$student_id]);
    $results = $stmt->fetchAll();
    echo "<h1>Query Success!</h1>Found " . count($results) . " courses.";
} catch (Exception $e) {
    echo "<h1>Query Failed!</h1>Error: " . $e->getMessage();
}
?>
