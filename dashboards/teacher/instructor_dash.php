<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkTeacherSubRole(ROLE_INSTRUCTOR);
require_once '../../includes/header.php';

$teacher_id = $_SESSION['user_id'];
// Fetch courses assigned to this instructor
$stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$courses = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Course Instructor Dashboard</h3></div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Stats -->
            <div class="col-lg-4 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3><?= count($courses) ?></h3>
                        <p>My Courses</p>
                    </div>
                    <div class="icon"><i class="bi bi-book"></i></div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header"><h3 class="card-title">Manage My Courses</h3></div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Course Code</th>
                            <th>Title</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($courses as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['course_code']) ?></td>
                            <td><?= htmlspecialchars($c['title']) ?></td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="upload_material.php?course_id=<?= $c['id'] ?>" class="btn btn-sm btn-info">Upload Lectures</a>
                                    <a href="attendance.php?course_id=<?= $c['id'] ?>" class="btn btn-sm btn-success">Manage Attendance</a>
                                    <a href="enrolled_students.php?course_id=<?= $c['id'] ?>" class="btn btn-sm btn-primary">Enrolled Students</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($courses)): ?>
                        <tr><td colspan="3" class="text-center">No courses assigned yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
