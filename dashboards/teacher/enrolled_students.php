<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkRole(['course_instructor', 'teacher', 'admin']);
require_once '../../core/db.php';
require_once '../../includes/header.php';

$course_id = $_GET['course_id'] ?? 0;
$teacher_id = $_SESSION['user_id'];

// Verify Ownership
if ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$course_id, $teacher_id]);
}
$course = $stmt->fetch();

if (!$course) {
    echo '<div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Enrolled Students</h3></div></div></div></div>';
    echo '<div class="app-content"><div class="container-fluid"><div class="alert alert-danger">Please select a valid course from your dashboard first.</div><a href="instructor_dash.php" class="btn btn-primary">Go to Dashboard</a></div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Fetch Students
$stmt = $pdo->prepare("
    SELECT u.name, u.email, u.registration_no, e.enrolled_at 
    FROM enrollments e 
    JOIN users u ON e.student_id = u.id 
    WHERE e.course_id = ?
");
$stmt->execute([$course_id]);
$students = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Enrolled Students: <?= htmlspecialchars($course['title']) ?></h3>
            </div>
            <div class="col-sm-6 text-end">
                <a href="instructor_dash.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Reg No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Enrolled Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['registration_no']) ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= date('M d, Y', strtotime($s['enrolled_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($students)): ?>
                            <tr><td colspan="4" class="text-center">No students enrolled yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
