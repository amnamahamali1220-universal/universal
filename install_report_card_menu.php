<?php
require_once __DIR__ . '/core/db.php';
try {
    $stmt = $pdo->prepare("SELECT id FROM sys_pages WHERE page_url = 'dashboards/student/report_card.php'");
    $stmt->execute();
    $page_id = $stmt->fetchColumn();
    if (!$page_id) {
        $insert = $pdo->prepare("INSERT INTO sys_pages (page_name, page_url, icon_class, parent_id, sort_order) 
                                 VALUES ('Report Card', 'dashboards/student/report_card.php', 'bi bi-file-earmark-pdf', 0, 99)");
        $insert->execute();
        $page_id = $pdo->lastInsertId();
    }
    
    $check = $pdo->prepare("SELECT * FROM role_access WHERE role_key = 'student' AND page_id = ?");
    $check->execute([$page_id]);
    if (!$check->fetch()) {
        $grant = $pdo->prepare("INSERT INTO role_access (role_key, page_id) VALUES ('student', ?)");
        $grant->execute([$page_id]);
    }
    echo "Menu added";
} catch (Exception $e) { echo $e->getMessage(); }
?>
