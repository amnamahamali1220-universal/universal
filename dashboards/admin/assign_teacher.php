<?php
require_once '../../core/session.php';
require_once '../../core/db.php';

if ($_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $teacher_id = $_POST['teacher_id'];
    
    $stmt = $pdo->prepare("UPDATE courses SET teacher_id = ? WHERE id = ?");
    $stmt->execute([$teacher_id, $course_id]);
    
    header("Location: courses.php");
}
?>
