<?php
require_once '../../core/session.php';
require_once '../../core/db.php';

$student_id = $_SESSION['user_id'] ?? 0;
$m_id = $_GET['id'] ?? 0;

if ($student_id && $m_id) {
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO material_views (student_id, material_id) VALUES (?, ?)");
        $stmt->execute([$student_id, $m_id]);
    } catch (Exception $e) {}
}

$stmt = $pdo->prepare("SELECT file_path, type FROM materials WHERE id = ?");
$stmt->execute([$m_id]);
$mat = $stmt->fetch();

if ($mat) {
    if ($mat['file_path']) {
        header("Location: " . BASE_URL . $mat['file_path']);
        exit;
    } else {
        // If it's a URL link instead of file
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    echo "Material not found.";
}
