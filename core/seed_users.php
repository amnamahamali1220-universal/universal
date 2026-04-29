<?php
require_once 'db.php';

try {
    echo "<h1>Seeding Universal ERP Database...</h1>";

    // 1. Create Tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS sys_roles (
        role_key VARCHAR(50) PRIMARY KEY,
        role_name VARCHAR(100) NOT NULL,
        is_system_role TINYINT(1) DEFAULT 0
    )");
    echo "Table 'sys_roles' checked/created.<br>";

    $pdo->exec("CREATE TABLE IF NOT EXISTS sys_pages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        parent_id INT DEFAULT 0,
        page_name VARCHAR(100) NOT NULL,
        page_url VARCHAR(255) NOT NULL,
        icon_class VARCHAR(100),
        sort_order INT DEFAULT 0
    )");
    echo "Table 'sys_pages' checked/created.<br>";

    $pdo->exec("CREATE TABLE IF NOT EXISTS role_access (
        role_key VARCHAR(50),
        page_id INT,
        PRIMARY KEY (role_key, page_id),
        FOREIGN KEY (role_key) REFERENCES sys_roles(role_key) ON DELETE CASCADE,
        FOREIGN KEY (page_id) REFERENCES sys_pages(id) ON DELETE CASCADE
    )");
    echo "Table 'role_access' checked/created.<br>";

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(50),
        identity_no VARCHAR(50),
        registration_no VARCHAR(50),
        is_active TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (role) REFERENCES sys_roles(role_key)
    )");
    echo "Table 'users' checked/created.<br>";

    // 2. Insert Roles
    // Check if roles exist before inserting to avoid duplicates if table existed
    $roles = [
        ['super_admin', 'Super Admin', 1],
        ['teacher', 'Teacher', 0],
        ['student', 'Student', 0]
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO sys_roles (role_key, role_name, is_system_role) VALUES (?, ?, ?)");
    foreach ($roles as $r) {
        $stmt->execute($r);
    }
    echo "Roles ensured.<br>";

    // 3. Insert Users
    $password = password_hash('123456', PASSWORD_DEFAULT); // Default password for all: 123456
    
    // We use a different password for admin just in case, or keep it simple? 
    // Plan said: admin123, etc. Let's stick to the plan.
    
    $users = [
        [
            'name' => 'Super Administrator',
            'email' => 'admin@universal.com',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'super_admin'
        ],
        [
            'name' => 'John Teacher',
            'email' => 'teacher@universal.com',
            'password' => password_hash('teacher123', PASSWORD_DEFAULT),
            'role' => 'teacher'
        ],
        [
            'name' => 'Jane Student',
            'email' => 'student@universal.com',
            'password' => password_hash('student123', PASSWORD_DEFAULT),
            'role' => 'student'
        ]
    ];

    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_active) VALUES (?, ?, ?, ?, 1)");

    foreach ($users as $u) {
        $checkStmt->execute([$u['email']]);
        if (!$checkStmt->fetch()) {
            $insertStmt->execute([$u['name'], $u['email'], $u['password'], $u['role']]);
            echo "User '{$u['email']}' ({$u['role']}) created.<br>";
        } else {
            echo "User '{$u['email']}' already exists.<br>";
        }
    }
    
    // Admin Pages Seeding
    function addPageLocal($pdo, $name, $url, $icon, $parentId = 0) {
        // Simple check to avoid duplicates
        $stmt = $pdo->prepare("SELECT id FROM sys_pages WHERE page_url = ? AND page_name = ?");
        $stmt->execute([$url, $name]);
        $id = $stmt->fetchColumn();
        
        if (!$id) {
            $stmt = $pdo->prepare("INSERT INTO sys_pages (parent_id, page_name, page_url, icon_class, sort_order) VALUES (?, ?, ?, ?, 0)");
            $stmt->execute([$parentId, $name, $url, $icon]);
            return $pdo->lastInsertId();
        }
        return $id;
    }
    
    function addAccessLocal($pdo, $role, $pageId) {
        $pdo->prepare("INSERT IGNORE INTO role_access (role_key, page_id) VALUES (?, ?)")
            ->execute([$role, $pageId]);
    }

    $adminHeaderId = addPageLocal($pdo, 'Administration', '#', 'bi bi-gear');
    $p1 = addPageLocal($pdo, 'Manage Users', 'dashboards/super_admin/manage_users.php', 'bi bi-people', $adminHeaderId);
    $p2 = addPageLocal($pdo, 'Manage Roles', 'dashboards/super_admin/manage_roles.php', 'bi bi-shield-lock', $adminHeaderId);
    $p3 = addPageLocal($pdo, 'Manage Pages', 'dashboards/super_admin/manage_pages.php', 'bi bi-window', $adminHeaderId);

    if($adminHeaderId && $p1 && $p2 && $p3) {
        addAccessLocal($pdo, 'super_admin', $adminHeaderId);
        addAccessLocal($pdo, 'super_admin', $p1);
        addAccessLocal($pdo, 'super_admin', $p2);
        addAccessLocal($pdo, 'super_admin', $p3);
        echo "Admin pages seeded.<br>";
    }

    echo "<h3>Seeding Complete!</h3>";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
