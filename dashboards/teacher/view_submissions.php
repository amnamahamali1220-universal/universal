<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkRole(['assignment_manager', 'teacher', 'admin']);
require_once '../../core/db.php';
require_once '../../includes/header.php';

$assignment_id = $_GET['assignment_id'] ?? 0;
$teacher_id = $_SESSION['user_id'];

// Verify Ownership
$stmt = $pdo->prepare("SELECT a.*, c.title as course_title FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.id = ? AND c.teacher_id = ?");
$stmt->execute([$assignment_id, $teacher_id]);
$assignment = $stmt->fetch();

if (!$assignment) {
    die("Access Denied or Assignment Not Found");
}

// Fetch Submissions
$sql = "SELECT s.*, u.name as student_name, u.registration_no 
        FROM submissions s 
        JOIN users u ON s.student_id = u.id 
        WHERE s.assignment_id = ? ORDER BY s.submitted_at DESC";
$subs = $pdo->prepare($sql);
$subs->execute([$assignment_id]);
$submissions = $subs->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Submissions: <?= htmlspecialchars($assignment['title']) ?></h3>
                <small class="text-muted"><?= htmlspecialchars($assignment['course_title']) ?></small>
            </div>
            <div class="col-sm-6 text-end">
                <a href="assignment_dash.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Reg No</th>
                            <th>Submitted At</th>
                            <th>Grade</th>
                            <th>File</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($submissions as $sub): ?>
                        <tr>
                            <td><?= htmlspecialchars($sub['student_name']) ?></td>
                            <td><?= htmlspecialchars($sub['registration_no']) ?></td>
                            <td><?= date('M d, Y H:i', strtotime($sub['submitted_at'])) ?></td>
                            <td>
                                <?php if($sub['grade'] !== null): ?>
                                    <span class="badge bg-success"><?= $sub['grade'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Not Graded</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= BASE_URL . $sub['file_path'] ?>" target="_blank" class="btn btn-sm btn-link"><i class="bi bi-file-earmark-arrow-down"></i> Download</a>
                            </td>
                            <td>
                                <a href="grade_assignment.php?id=<?= $assignment_id ?>" class="btn btn-sm btn-primary">Grade</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($submissions)): ?>
                            <tr><td colspan="6" class="text-center">No submissions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
