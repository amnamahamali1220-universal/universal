<?php
require_once '../core/db.php';
try {
    $pdo->exec("ALTER TABLE courses ADD COLUMN IF NOT EXISTS course_code VARCHAR(50) NULL AFTER teacher_id");
    echo "<h1>Database Updated Successfully!</h1>";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
