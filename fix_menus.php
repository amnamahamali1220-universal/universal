<?php
require_once 'core/db.php';

// 1. Create proper headers if they don't exist (Ignoring the helper function bug for now)
try {
    // Insert Teacher Dashboard Header
    $pdo->prepare("INSERT INTO sys_pages (page_name, page_url, icon_class, sort_order, parent_id) VALUES (?, ?, ?, ?, 0)")
        ->execute(['Teacher Dashboard', '#', 'bi bi-person-workspace', 5]);
    $teacherHeaderId = $pdo->lastInsertId();
    echo "Created Teacher Header: ID $teacherHeaderId\n";

    // Insert Student Area Header
    $pdo->prepare("INSERT INTO sys_pages (page_name, page_url, icon_class, sort_order, parent_id) VALUES (?, ?, ?, ?, 0)")
        ->execute(['Student Area', '#', 'bi bi-mortarboard', 10]);
    $studentHeaderId = $pdo->lastInsertId();
    echo "Created Student Header: ID $studentHeaderId\n";

    // 2. Move existing pages to new headers
    // My Courses (URL: dashboards/teacher/my_courses.php) -> Teacher Header
    $pdo->prepare("UPDATE sys_pages SET parent_id = ? WHERE page_url = ?")
        ->execute([$teacherHeaderId, 'dashboards/teacher/my_courses.php']);

    // Create Course (URL: dashboards/teacher/create_course.php) -> Teacher Header
    $pdo->prepare("UPDATE sys_pages SET parent_id = ? WHERE page_url = ?")
        ->execute([$teacherHeaderId, 'dashboards/teacher/create_course.php']);

    // My Learning (URL: dashboards/student/my_learning.php) -> Student Header
    $pdo->prepare("UPDATE sys_pages SET parent_id = ? WHERE page_url = ?")
        ->execute([$studentHeaderId, 'dashboards/student/my_learning.php']);

    // 3. Fix Permissions
    // Teacher Role needs access to new Teacher Header
    $pdo->prepare("INSERT INTO role_access (role_key, page_id) VALUES ('teacher', ?)")->execute([$teacherHeaderId]);
    
    // Student Role needs access to new Student Header
    $pdo->prepare("INSERT INTO role_access (role_key, page_id) VALUES ('student', ?)")->execute([$studentHeaderId]);

    // Also remove the wrong access to "System Management" (ID 2) for Teacher/Student if desired, 
    // but better to leave it if it serves other purposes. 
    // Actually, checking previous run, ID 2 was granted. Let's remove it to avoid confusion if they don't need it.
    // $pdo->exec("DELETE FROM role_access WHERE page_id = 2 AND role_key IN ('teacher', 'student')");

    echo "Menu structure fixed!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
