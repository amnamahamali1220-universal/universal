<?php
require_once 'core/db.php';

function addPage($pdo, $name, $url, $icon, $parentId = 0) {
    $stmt = $pdo->prepare("SELECT id FROM sys_pages WHERE page_url = ? AND page_name = ?");
    $stmt->execute([$url, $name]);
    $id = $stmt->fetchColumn();
    
    if (!$id) {
        $stmt = $pdo->prepare("INSERT INTO sys_pages (parent_id, page_name, page_url, icon_class, sort_order) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$parentId, $name, $url, $icon]);
        $id = $pdo->lastInsertId();
        echo "Added Page: $name\n";
    }
    return $id;
}

function addPermission($pdo, $role, $pageId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM role_access WHERE role_key = ? AND page_id = ?");
    $stmt->execute([$role, $pageId]);
    if ($stmt->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO role_access (role_key, page_id) VALUES (?, ?)")->execute([$role, $pageId]);
        echo "Granted Access: $role -> Page ID $pageId\n";
    }
}

try {
    echo "Registering Admin RBAC Pages...\n";

    // 1. Admin Header
    $adminHeaderId = addPage($pdo, 'Admin Dashboard', '#', 'bi bi-speedometer2');
    
    // 2. Admin Sub-pages
    $p1 = addPage($pdo, 'Dashboard Home', 'dashboards/admin/index.php', 'bi bi-house', $adminHeaderId);
    $p2 = addPage($pdo, 'Manage Teachers', 'dashboards/admin/list_teachers.php', 'bi bi-people', $adminHeaderId);
    $p3 = addPage($pdo, 'Manage Students', 'dashboards/admin/students.php', 'bi bi-mortarboard', $adminHeaderId);
    $p4 = addPage($pdo, 'Manage Courses', 'dashboards/admin/courses.php', 'bi bi-book', $adminHeaderId);

    // 3. Grant Permissions to Admin
    addPermission($pdo, 'admin', $adminHeaderId);
    addPermission($pdo, 'admin', $p1);
    addPermission($pdo, 'admin', $p2);
    addPermission($pdo, 'admin', $p3);
    addPermission($pdo, 'admin', $p4);

    echo "RBAC Menu Registration Complete!\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
