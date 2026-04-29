<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/teacher_roles.php';

// Sync role if teacher
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'teacher') {
    syncTeacherRole();
}

// Get the current script name (e.g., 'login.php')
$current_page = basename($_SERVER['PHP_SELF']);

// List of pages that DO NOT require login
$public_pages = ['login.php', 'register.php'];

// Auth Check Logic
if (!isset($_SESSION['user_id'])) {
    // If user is NOT logged in AND trying to access a protected page
    if (!in_array($current_page, $public_pages)) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
} 
// Conflict Fix: If user IS logged in but tries to go to Login/Register, send them to Dashboard
else {
    if (in_array($current_page, $public_pages)) {
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'student') {
            header("Location: " . BASE_URL . "dashboards/student/index.php");
        } elseif (isset($_SESSION['role']) && ($_SESSION['role'] === 'teacher' || in_array($_SESSION['role'], ['course_instructor', 'assignment_manager', 'exam_controller', 'senior_teacher']))) {
            header("Location: " . BASE_URL . "dashboards/teacher/index.php");
        } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            header("Location: " . BASE_URL . "dashboards/admin/index.php");
        } else {
            header("Location: " . BASE_URL . "index.php");
        }
        exit;
    }
}

// Helper to determine color based on role string
function getRoleBadgeColor($roleName) {
    $colors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'dark'];
    $index = crc32($roleName) % count($colors);
    return 'text-bg-' . $colors[$index];
}

/**
 * Enforce Role Based Access Control
 * @param array|string $allowed_roles
 */
function checkRole($allowed_roles) {
    if (!isset($_SESSION['role'])) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
    
    // Always allow super_admin
    if ($_SESSION['role'] === 'super_admin') {
        return;
    }
    
    $allowed = is_array($allowed_roles) ? $allowed_roles : [$allowed_roles];
    
    // Support teacher sub-roles: if 'teacher' is allowed, allow all sub-roles
    $teacher_sub_roles = ['course_instructor', 'assignment_manager', 'exam_controller', 'senior_teacher'];
    if (in_array('teacher', $allowed)) {
        $allowed = array_merge($allowed, $teacher_sub_roles);
    }

    if (!in_array($_SESSION['role'], $allowed)) {
        // Redirect to their own dashboard or show error
        if ($_SESSION['role'] === 'admin') {
            header("Location: " . BASE_URL . "dashboards/admin/index.php");
        } elseif ($_SESSION['role'] === 'teacher') {
            header("Location: " . BASE_URL . "dashboards/teacher/index.php");
        } elseif ($_SESSION['role'] === 'student') {
            header("Location: " . BASE_URL . "dashboards/student/index.php");
        } else {
            header("Location: " . BASE_URL . "login.php");
        }
        exit;
    }
}
?>