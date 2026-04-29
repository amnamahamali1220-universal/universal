<?php
require_once 'db.php';

function addRole($pdo, $key, $name) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM sys_roles WHERE role_key = ?");
    $stmt->execute([$key]);
    if ($stmt->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO sys_roles (role_key, role_name) VALUES (?, ?)")->execute([$key, $name]);
        echo "Added Role: $name<br>";
    }
}

function addPage($pdo, $name, $url, $icon, $parentId = 0) {
    $stmt = $pdo->prepare("SELECT id FROM sys_pages WHERE page_url = ?");
    $stmt->execute([$url]);
    $id = $stmt->fetchColumn();
    
    if (!$id) {
        $stmt = $pdo->prepare("INSERT INTO sys_pages (parent_id, page_name, page_url, icon_class, sort_order) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$parentId, $name, $url, $icon]);
        $id = $pdo->lastInsertId();
        echo "Added Page: $name<br>";
    } else {
        // If parentId is 0 (it's a header) and we found a match, check if names match too
        // Actually, for idempotency, if we find a URL match but it's a generic link '#', we might get the wrong ID.
        // A better fix is to check page_name AND page_url for headers.
        if ($url === '#') {
             $stmt = $pdo->prepare("SELECT id FROM sys_pages WHERE page_url = ? AND page_name = ?");
             $stmt->execute([$url, $name]);
             $specificId = $stmt->fetchColumn();
             if ($specificId) $id = $specificId;
             else {
                // Create new header if specific header not found
                $stmt = $pdo->prepare("INSERT INTO sys_pages (parent_id, page_name, page_url, icon_class, sort_order) VALUES (?, ?, ?, ?, 0)");
                $stmt->execute([$parentId, $name, $url, $icon]);
                $id = $pdo->lastInsertId();
                echo "Added Page (Specific Header): $name<br>";
             }
        }
    }
    return $id;
}

function addPermission($pdo, $role, $pageId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM role_access WHERE role_key = ? AND page_id = ?");
    $stmt->execute([$role, $pageId]);
    if ($stmt->fetchColumn() == 0) {
        $pdo->prepare("INSERT INTO role_access (role_key, page_id) VALUES (?, ?)")->execute([$role, $pageId]);
        echo "Granted Access: $role -> Page ID $pageId<br>";
    }
}

try {
    echo "<h1>Initializing Student CMS Database...</h1>";

    // 1. Create Tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS course_materials (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT NOT NULL,
        type ENUM('pdf', 'video', 'text', 'assignment') NOT NULL,
        title VARCHAR(255) NOT NULL,
        file_path VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS student_enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        course_id INT NOT NULL,
        enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )");

    echo "Tables courses, course_materials, student_enrollments checked/created.<br>";

    // 2. Add Roles
    addRole($pdo, 'student', 'Student');
    addRole($pdo, 'teacher', 'Teacher');

    // 3. Add Pages & Headers
    // Teacher Menu
    $teacherHeaderId = addPage($pdo, 'Teacher Dashboard', '#', 'bi bi-person-workspace');
    $p1 = addPage($pdo, 'My Courses', 'dashboards/teacher/my_courses.php', 'bi bi-book', $teacherHeaderId);
    $p2 = addPage($pdo, 'Create Course', 'dashboards/teacher/create_course.php', 'bi bi-plus-circle', $teacherHeaderId);
    
    // Student Menu
    $studentHeaderId = addPage($pdo, 'Student Area', '#', 'bi bi-mortarboard');
    $p3 = addPage($pdo, 'My Learning', 'dashboards/student/my_learning.php', 'bi bi-journal-bookmark', $studentHeaderId);

    // 4. Permissions
    addPermission($pdo, 'teacher', $teacherHeaderId);
    addPermission($pdo, 'teacher', $p1);
    addPermission($pdo, 'teacher', $p2);

    addPermission($pdo, 'student', $studentHeaderId);
    addPermission($pdo, 'student', $p3);

    echo "<h3>Setup Complete!</h3>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
