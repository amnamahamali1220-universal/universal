<?php
require_once 'core/db.php';

$sql = "CREATE TABLE IF NOT EXISTS `academic_events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `event_type` ENUM('Exam', 'Holiday', 'Academic Event') NOT NULL DEFAULT 'Academic Event',
    `start_date` DATETIME NOT NULL,
    `end_date` DATETIME NULL,
    `created_by` INT(11) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

try {
    $pdo->exec($sql);
    echo "SUCCESS: academic_events table created or already exists.";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
