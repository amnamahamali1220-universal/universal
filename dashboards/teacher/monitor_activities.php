<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkRole(['senior_teacher', 'admin']);
require_once '../../core/db.php';
require_once '../../includes/header.php';

$course_id = $_GET['course_id'] ?? 0;

// Fetch Course Details
$stmt = $pdo->prepare("SELECT c.*, u.name as instructor_name FROM courses c JOIN users u ON c.teacher_id = u.id WHERE c.id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    echo '<div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Monitor Activities</h3></div></div></div></div>';
    echo '<div class="app-content"><div class="container-fluid"><div class="alert alert-danger">Course Not Found. Please select a valid course.</div><a href="senior_dash.php" class="btn btn-primary">Go to Dashboard</a></div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Fetch Recent Materials for this course
$stmt = $pdo->prepare("SELECT * FROM materials WHERE course_id = ? ORDER BY uploaded_at DESC LIMIT 10");
$stmt->execute([$course_id]);
$materials = $stmt->fetchAll();

// Fetch Recent Assignments
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE course_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$course_id]);
$assignments = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Monitoring: <?= htmlspecialchars($course['title']) ?></h3>
                <small class="text-muted">Instructor: <?= htmlspecialchars($course['instructor_name']) ?></small>
            </div>
            <div class="col-sm-6 text-end">
                <a href="senior_dash.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-info">
                    <div class="card-header"><h3 class="card-title">Recent Materials</h3></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Title</th><th>Type</th><th>Date</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($materials as $m): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($m['title']) ?></td>
                                        <td><?= strtoupper($m['type']) ?></td>
                                        <td><?= date('M d', strtotime($m['uploaded_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-outline card-warning">
                    <div class="card-header"><h3 class="card-title">Recent Assignments</h3></div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Title</th><th>Due Date</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($assignments as $a): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($a['title']) ?></td>
                                        <td><?= date('M d', strtotime($a['due_date'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
