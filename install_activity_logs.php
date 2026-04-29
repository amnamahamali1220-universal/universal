<?php
require_once __DIR__ . '/core/db.php';

try {
    // 1. Create table
    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        action VARCHAR(255) NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // 2. Insert into sys_pages if not exists
    $stmt = $pdo->prepare("SELECT id FROM sys_pages WHERE page_url = 'dashboards/admin/activity_logs.php'");
    $stmt->execute();
    $page_id = $stmt->fetchColumn();
    
    if (!$page_id) {
        $insert = $pdo->prepare("INSERT INTO sys_pages (page_name, page_url, icon_class, parent_id, sort_order) 
                                 VALUES ('Activity Logs', 'dashboards/admin/activity_logs.php', 'bi bi-journal-text', 0, 99)");
        $insert->execute();
        $page_id = $pdo->lastInsertId();
    }
    
    // 3. Grant role_access to super_admin and admin
    $roles = ['super_admin', 'admin'];
    foreach ($roles as $role) {
        $check = $pdo->prepare("SELECT * FROM role_access WHERE role_key = ? AND page_id = ?");
        $check->execute([$role, $page_id]);
        if (!$check->fetchColumn()) {
            $grant = $pdo->prepare("INSERT INTO role_access (role_key, page_id) VALUES (?, ?)");
            $grant->execute([$role, $page_id]);
        }
    }
    
    echo "Activity logs module installed successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
