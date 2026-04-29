<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkRole(['exam_controller', 'teacher', 'admin']);
require_once '../../core/db.php';
require_once '../../includes/header.php';

$quiz_id = $_GET['quiz_id'] ?? 0;
$teacher_id = $_SESSION['user_id'];

// Verify Ownership
$stmt = $pdo->prepare("SELECT q.*, c.title as course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE q.id = ? AND c.teacher_id = ?");
$stmt->execute([$quiz_id, $teacher_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("Access Denied or Quiz Not Found");
}

// Fetch Attempted Results
$stmt = $pdo->prepare("
    SELECT qa.*, u.name as student_name, u.registration_no 
    FROM quiz_attempts qa 
    JOIN users u ON qa.student_id = u.id 
    WHERE qa.quiz_id = ? ORDER BY qa.score DESC
");
$stmt->execute([$quiz_id]);
$attempts = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Quiz Results: <?= htmlspecialchars($quiz['title']) ?></h3>
                <small class="text-muted"><?= htmlspecialchars($quiz['course_title']) ?></small>
            </div>
            <div class="col-sm-6 text-end">
                <a href="exam_dash.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-info">
            <div class="card-header"><h3 class="card-title">Student Performance</h3></div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Reg No</th>
                            <th>Student Name</th>
                            <th>Score</th>
                            <th>Attempted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($attempts as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['registration_no']) ?></td>
                            <td><?= htmlspecialchars($a['student_name']) ?></td>
                            <td><span class="badge bg-success"><?= $a['score'] ?></span></td>
                            <td><?= date('M d, Y H:i', strtotime($a['attempted_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($attempts)): ?>
                            <tr><td colspan="4" class="text-center">No students have attempted this quiz yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
