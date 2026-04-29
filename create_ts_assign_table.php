<?php
require_once 'c:/xampp/htdocs/universal/core/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS teacher_student_assign (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT NOT NULL,
        student_id INT NOT NULL,
        assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_assignment (teacher_id, student_id)
    )";
    $pdo->exec($sql);
    echo "Table teacher_student_assign created successfully.";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}
?>
