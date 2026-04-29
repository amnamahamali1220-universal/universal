<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkTeacherSubRole(ROLE_SENIOR);
require_once '../../includes/header.php';

// Senior teachers can monitor everything
$course_count = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$teacher_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$assignment_count = $pdo->query("SELECT COUNT(*) FROM assignments")->fetchColumn();

// Recent activities (Example)
$stmt = $pdo->query("SELECT * FROM courses ORDER BY created_at DESC LIMIT 5");
$recent_courses = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Senior Teacher Dashboard</h3></div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3><?= $course_count ?></h3>
                        <p>Total Courses</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3><?= $teacher_count ?></h3>
                        <p>Total Teachers</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3><?= $assignment_count ?></h3>
                        <p>Total Assignments</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header"><h3 class="card-title">Monitor Teacher Activities & Courses</h3></div>
            <div class="card-body">
                <h5>Recent Courses</h5>
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Instructor ID</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_courses as $rc): ?>
                        <tr>
                            <td><?= htmlspecialchars($rc['title']) ?></td>
                            <td><?= htmlspecialchars($rc['teacher_id']) ?></td>
                            <td><span class="badge bg-success">Approved</span></td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="course_view.php?id=<?= $rc['id'] ?>" class="btn btn-sm btn-primary">View Course</a>
                                    <a href="monitor_activities.php?course_id=<?= $rc['id'] ?>" class="btn btn-sm btn-success">Monitor Activity</a>
                                    <button class="btn btn-sm btn-info">Approve Content</button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
