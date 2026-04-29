<?php
require_once __DIR__ . '/core/db.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message VARCHAR(255) NOT NULL,
        link VARCHAR(255) DEFAULT '#',
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS material_views (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        material_id INT NOT NULL,
        viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY student_mat (student_id, material_id)
    )");

    echo "Workflow tables installed.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
