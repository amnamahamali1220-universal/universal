<?php 
require_once 'includes/header.php'; 
require_once 'includes/visual_helper.php';
?>
<link rel="stylesheet" href="assets/css/dashboard_enhancements.css">
<?= getChartScripts() ?>

<?php
// Fetch Counts for Admin
$userCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$roleCount = $pdo->query("SELECT COUNT(*) FROM sys_roles")->fetchColumn();
$pageCount = $pdo->query("SELECT COUNT(*) FROM sys_pages")->fetchColumn();

// Extended Stats for Charts
$studentCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$teacherCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$courseCount = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$submissionCount = $pdo->query("SELECT COUNT(*) FROM submissions")->fetchColumn();

// Fetch specific data for current user
$myPerms = $pdo->prepare("SELECT COUNT(*) FROM role_access WHERE role_key = ?");
$myPerms->execute([$_SESSION['role']]);
$myPermCount = $myPerms->fetchColumn();
?>

<!-- Welcome Banner -->
<div class="welcome-banner">
    <div class="welcome-content">
        <h1>Student Content Management System Dashboard</h1>
        <p>Manage Students, Courses, Assignments and Exams Easily</p>
    </div>
    <div class="welcome-bg-shape"></div>
    <img src="assets/img/dashboard_banner.png" alt="Education Illustration" class="welcome-illustration">
</div>

<div class="row">
    <?php if($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin'): ?>
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-primary">
            <div class="inner">
                <h3><?= $userCount ?></h3>
                <p>Total Users</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
            <a href="dashboards/super_admin/manage_users.php" class="small-box-footer">More info <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-success">
            <div class="inner">
                <h3><?= $roleCount ?></h3>
                <p>System Roles</p>
            </div>
            <div class="icon">
                <i class="fas fa-shield-halved"></i>
            </div>
            <a href="dashboards/super_admin/manage_roles.php" class="small-box-footer">More info <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-warning">
            <div class="inner">
                <h3><?= $pageCount ?></h3>
                <p>Dynamic Pages</p>
            </div>
            <div class="icon">
                <i class="fas fa-file-code"></i>
            </div>
            <a href="dashboards/super_admin/manage_pages.php" class="small-box-footer">More info <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
    <?php endif; ?>

    <div class="col-lg-3 col-6">
        <div class="small-box text-bg-info">
            <div class="inner">
                <h3><?= $myPermCount ?></h3>
                <p>Access Privileges</p>
            </div>
            <div class="icon">
                <i class="fas fa-key"></i>
            </div>
            <a href="#" class="small-box-footer">View Access <i class="bi bi-arrow-right-circle"></i></a>
        </div>
    </div>
</div>

<!-- Quick Actions Panel -->
<div class="quick-actions-panel mb-4">
    <h5 class="mb-3"><i class="fas fa-bolt me-2 text-warning"></i> Quick Actions</h5>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <a href="dashboards/admin/students.php" class="quick-action-btn qa-student">
                <i class="fas fa-user-plus text-primary"></i>
                <span>Add Student</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="dashboards/admin/courses.php" class="quick-action-btn qa-course">
                <i class="fas fa-book-medical text-success"></i>
                <span>Add Course</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="dashboards/teacher/upload_material.php" class="quick-action-btn qa-material">
                <i class="fas fa-cloud-upload-alt text-info"></i>
                <span>Upload Material</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="dashboards/teacher/create_assignment.php" class="quick-action-btn qa-assignment">
                <i class="fas fa-tasks text-warning"></i>
                <span>Create Assignment</span>
            </a>
        </div>
    </div>
</div>

<!-- System Statistics Chart -->
<div class="row">
    <div class="col-12">
        <div class="card chart-card">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title"><i class="fas fa-chart-bar me-2"></i> System Statistics Overview</h3>
            </div>
            <div class="card-body">
                <canvas id="systemStatsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('systemStatsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Students', 'Teachers', 'Courses', 'Assignments Submitted'],
            datasets: [{
                label: 'System Counts',
                data: [<?= $studentCount ?>, <?= $teacherCount ?>, <?= $courseCount ?>, <?= $submissionCount ?>],
                backgroundColor: [
                    'rgba(66, 153, 225, 0.8)', // Professional Blue
                    'rgba(72, 187, 120, 0.8)', // Sage Green
                    'rgba(237, 137, 54, 0.8)',  // Education Orange
                    'rgba(159, 122, 234, 0.8)'  // Subject Purple
                ],
                borderColor: [
                    '#3182ce',
                    '#38a169',
                    '#dd6b20',
                    '#805ad5'
                ],
                borderWidth: 1,
                borderRadius: 5
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        color: '#cbd5e0' // Light Grey Ticks
                    },
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)' // Subtle Grid Lines
                    }
                },
                x: {
                    ticks: {
                        color: '#cbd5e0' // Light Grey Ticks
                    },
                    grid: {
                        display: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>