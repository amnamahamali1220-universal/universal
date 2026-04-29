<?php
require_once '../../core/session.php';
checkRole('teacher');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$quiz_id = $_GET['id'] ?? 0;
$course_id = $_GET['course_id'] ?? 0;

// Fetch Quiz Info
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    die("Quiz not found.");
}

// Handle Manual Grade Update
if (isset($_POST['update_grade'])) {
    $attempt_id = $_POST['attempt_id'];
    $new_score = $_POST['new_score'];
    $stmt = $pdo->prepare("UPDATE quiz_attempts SET score = ? WHERE id = ?");
    $stmt->execute([$new_score, $attempt_id]);
    echo "<script>alert('Grade Updated');</script>";
}

// Fetch Results
$sql = "SELECT qa.*, u.name as student_name, u.registration_no 
        FROM quiz_attempts qa 
        JOIN users u ON qa.student_id = u.id 
        WHERE qa.quiz_id = ? 
        ORDER BY qa.attempted_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$quiz_id]);
$results = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3>Quiz Results: <?= htmlspecialchars($quiz['title']) ?></h3>
            </div>
            <div class="col-sm-6 text-end">
                <a href="quizzes.php?course_id=<?= $course_id ?>" class="btn btn-secondary">Back to Quizzes</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Reg No</th>
                                <th>Score (%)</th>
                                <th>Attempted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($results as $r): ?>
                            <tr>
                                <td><?= htmlspecialchars($r['student_name']) ?></td>
                                <td><?= htmlspecialchars($r['registration_no']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $r['score'] >= 50 ? 'success' : 'danger' ?>">
                                        <?= number_format($r['score'], 1) ?>%
                                    </span>
                                </td>
                                <td><?= date('M d, Y h:i A', strtotime($r['attempted_at'])) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editGradeModal<?= $r['id'] ?>">
                                        <i class="bi bi-pencil-square"></i> Update Mark
                                    </button>

                                    <!-- Edit Grade Modal -->
                                    <div class="modal fade" id="editGradeModal<?= $r['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <form method="post" class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Update Mark for <?= htmlspecialchars($r['student_name']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Score (%)</label>
                                                        <input type="number" step="0.1" name="new_score" class="form-control" value="<?= $r['score'] ?>" required>
                                                    </div>
                                                    <input type="hidden" name="attempt_id" value="<?= $r['id'] ?>">
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" name="update_grade" class="btn btn-success">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($results)): ?>
                                <tr><td colspan="5" class="text-center text-muted p-4">No submissions yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
