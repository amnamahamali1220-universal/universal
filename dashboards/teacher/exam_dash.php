<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkTeacherSubRole(ROLE_EXAM);
require_once '../../includes/header.php';

$teacher_id = $_SESSION['user_id'];
// Fetch quizzes managed by this teacher
$stmt = $pdo->prepare("SELECT q.*, c.title as course_title FROM quizzes q JOIN courses c ON q.course_id = c.id WHERE c.teacher_id = ?");
$stmt->execute([$teacher_id]);
$quizzes = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Exam Controller Dashboard</h3></div>
            <div class="col-sm-6 text-end">
                <a href="create_quiz.php" class="btn btn-primary">Create New Quiz</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box text-bg-danger">
                    <div class="inner">
                        <h3><?= count($quizzes) ?></h3>
                        <p>Total Quizzes</p>
                    </div>
                    <div class="icon"><i class="bi bi-patch-check"></i></div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header"><h3 class="card-title">Manage Quizzes & Exams</h3></div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Quiz Title</th>
                            <th>Course</th>
                            <th>Published Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($quizzes as $q): ?>
                        <tr>
                            <td><?= htmlspecialchars($q['title']) ?></td>
                            <td><?= htmlspecialchars($q['course_title']) ?></td>
                            <td><?= htmlspecialchars($q['created_at']) ?></td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="edit_quiz.php?id=<?= $q['id'] ?>" class="btn btn-sm btn-warning">Edit Questions</a>
                                    <a href="publish_results.php?quiz_id=<?= $q['id'] ?>" class="btn btn-sm btn-info">Publish Results</a>
                                    <a href="exam_schedule.php" class="btn btn-sm btn-secondary">Upload Schedule</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($quizzes)): ?>
                        <tr><td colspan="4" class="text-center">No quizzes created yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
