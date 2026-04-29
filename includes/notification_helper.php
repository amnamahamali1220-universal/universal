<?php
function notifyCourseStudents($pdo, $course_id, $message, $link) {
    if (!$pdo || !$course_id) return;
    try {
        $stmt = $pdo->prepare("SELECT student_id FROM enrollments WHERE course_id = ?");
        $stmt->execute([$course_id]);
        $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (?, ?, ?)");
        while ($row = $stmt->fetch()) {
            $notif_stmt->execute([$row['student_id'], $message, $link]);
        }
    } catch (PDOException $e) {
        error_log("Notify error: " . $e->getMessage());
    }
}
?>
