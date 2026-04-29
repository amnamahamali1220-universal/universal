<?php
require_once '../core/db.php';

function registerPage($pdo, $name, $url, $icon, $parentId = 0, $sort = 0) {
    // Check if exists
    $stmt = $pdo->prepare("SELECT id FROM sys_pages WHERE page_url = ? AND page_name = ?");
    $stmt->execute([$url, $name]);
    $id = $stmt->fetchColumn();

    if (!$id) {
        $stmt = $pdo->prepare("INSERT INTO sys_pages (parent_id, page_name, page_url, icon_class, sort_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$parentId, $name, $url, $icon, $sort]);
        $id = $pdo->lastInsertId();
        echo "Registered Page: $name<br>";
    }
    return $id;
}

function grantAccess($pdo, $role, $pageId) {
    if (!$pageId) return;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM role_access WHERE role_key = ? AND page_id = ?");
    $stmt->execute([$role, $pageId]);
    if ($stmt->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO role_access (role_key, page_id) VALUES (?, ?)");
        $stmt->execute([$role, $pageId]);
    }
}

echo "<h1>Updating System Navigation...</h1>";

// --- Admin Pages ---
$adminHeader = registerPage($pdo, 'Admin Panel', '#', 'bi bi-shield-lock');
grantAccess($pdo, 'admin', $adminHeader);

$p1 = registerPage($pdo, 'Dashboard', 'dashboards/admin/index.php', 'bi bi-speedometer', $adminHeader, 1);
$p2 = registerPage($pdo, 'Manage Students', 'dashboards/admin/students.php', 'bi bi-people', $adminHeader, 2);
$p3 = registerPage($pdo, 'Manage Teachers', 'dashboards/admin/teachers.php', 'bi bi-person-video3', $adminHeader, 3);
$p4 = registerPage($pdo, 'Manage Courses', 'dashboards/admin/courses.php', 'bi bi-book', $adminHeader, 4);

grantAccess($pdo, 'admin', $p1);
grantAccess($pdo, 'admin', $p2);
grantAccess($pdo, 'admin', $p3);
grantAccess($pdo, 'admin', $p4);

// --- Teacher Pages ---
$teacherHeader = registerPage($pdo, 'Teacher Menu', '#', 'bi bi-person-workspace');
// Note: We might have existing pages from previous setups, this effectively ensures they exist
$t1 = registerPage($pdo, 'Dashboard', 'dashboards/teacher/index.php', 'bi bi-speedometer2', $teacherHeader, 1);
$t2 = registerPage($pdo, 'My Courses', 'dashboards/teacher/my_courses.php', 'bi bi-journal-text', $teacherHeader, 2);
$t3 = registerPage($pdo, 'Student Reports', 'dashboards/teacher/reports.php', 'bi bi-graph-up', $teacherHeader, 3);

grantAccess($pdo, 'teacher', $teacherHeader);
grantAccess($pdo, 'teacher', $t1);
grantAccess($pdo, 'teacher', $t2);
grantAccess($pdo, 'teacher', $t3);

// --- Student Pages ---
$studentHeader = registerPage($pdo, 'Student Area', '#', 'bi bi-mortarboard');
$s1 = registerPage($pdo, 'My Learning', 'dashboards/student/my_learning.php', 'bi bi-journal-bookmark', $studentHeader, 1);
$s2 = registerPage($pdo, 'Browse Courses', 'dashboards/student/browse_courses.php', 'bi bi-search', $studentHeader, 2);
$s3 = registerPage($pdo, 'My Grades', 'dashboards/student/grades.php', 'bi bi-award', $studentHeader, 3);

grantAccess($pdo, 'student', $studentHeader);
grantAccess($pdo, 'student', $s1);
grantAccess($pdo, 'student', $s2);
grantAccess($pdo, 'student', $s3);

// --- Communication (All Roles) ---
$commHeader = registerPage($pdo, 'Communication', '#', 'bi bi-chat-dots', 0, 99);
$c1 = registerPage($pdo, 'Messages', 'dashboards/communication/messages.php', 'bi bi-envelope', $commHeader, 1);

grantAccess($pdo, 'admin', $commHeader);
grantAccess($pdo, 'admin', $c1);

grantAccess($pdo, 'teacher', $commHeader);
grantAccess($pdo, 'teacher', $c1);

grantAccess($pdo, 'student', $commHeader);
grantAccess($pdo, 'student', $c1);

echo "<h3>Navigation Updated Successfully!</h3>";
?>
