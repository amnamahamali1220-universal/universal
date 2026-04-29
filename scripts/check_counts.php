<?php
require_once 'core/db.php';
foreach(['materials','course_materials','enrollments','student_enrollments'] as $t) {
    try {
        $c = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
        echo "$t: $c\n";
    } catch (Exception $e) {
        echo "$t: Error ({$e->getMessage()})\n";
    }
}
?>
