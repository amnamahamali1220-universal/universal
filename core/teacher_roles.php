<?php
/**
 * Teacher Role Management System
 * Extends the existing RBAC to support teacher sub-roles.
 */

require_once __DIR__ . '/db.php';

// Teacher Sub-Role Constants
if (!defined('ROLE_INSTRUCTOR')) define('ROLE_INSTRUCTOR', 'course_instructor');
if (!defined('ROLE_ASSIGNMENT')) define('ROLE_ASSIGNMENT', 'assignment_manager');
if (!defined('ROLE_EXAM')) define('ROLE_EXAM', 'exam_controller');
if (!defined('ROLE_SENIOR')) define('ROLE_SENIOR', 'senior_teacher');

/**
 * Check if the logged-in teacher has a specific sub-role.
 * Redirects if not permitted.
 */
function checkTeacherSubRole($required_role) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] === 'student' || $_SESSION['role'] === 'admin') {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }

    // super_admin always allowed
    if ($_SESSION['role'] === 'super_admin') return;

    global $pdo;
    $stmt = $pdo->prepare("SELECT teacher_role FROM teachers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $sub_role = $stmt->fetchColumn();

    if ($sub_role !== $required_role) {
        // Find their actual dashboard
        if ($sub_role) {
            header("Location: " . BASE_URL . "dashboards/teacher/" . getTeacherDashboard($sub_role));
        } else {
            header("Location: " . BASE_URL . "dashboards/teacher/index.php");
        }
        exit;
    }
}

/**
 * Get the dashboard filename for a given teacher role.
 */
function getTeacherDashboard($role) {
    $dashboards = [
        ROLE_INSTRUCTOR => 'instructor_dash.php',
        ROLE_ASSIGNMENT => 'assignment_dash.php',
        ROLE_EXAM       => 'exam_dash.php',
        ROLE_SENIOR     => 'senior_dash.php'
    ];
    return $dashboards[$role] ?? 'index.php';
}

/**
 * Synchronize session role with teacher sub-role if applicable.
 * This ensures the dynamic sidebar picks up the sub-role menu.
 */
function syncTeacherRole() {
    static $synced = false;
    if ($synced) return;
    
    if (isset($_SESSION['user_id'])) {
        global $pdo;
        // Check if this user is a teacher in the core users table
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $core_role = $stmt->fetchColumn();

        if ($core_role === 'teacher') {
            $stmt = $pdo->prepare("SELECT teacher_role FROM teachers WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $sub_role = $stmt->fetchColumn();
            
            if ($sub_role) {
                $_SESSION['role'] = $sub_role;
            }
            $synced = true;
        }
    }
}
?>
