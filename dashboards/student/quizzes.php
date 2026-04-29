<?php
require_once '../../core/session.php';
checkRole('student');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$student_id = $_SESSION['user_id'];

// Fetch Quizzes from enrolled courses
$sql = "SELECT q.*, c.title as course_title, c.course_code,
            (SELECT score FROM quiz_attempts WHERE quiz_id = q.id AND student_id = ?) as my_score
        FROM quizzes q
        JOIN courses c ON q.course_id = c.id
        JOIN enrollments e ON c.id = e.course_id
        WHERE e.student_id = ?
        ORDER BY q.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id, $student_id]);
$quizzes = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>My Quizzes</h3></div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <?php foreach($quizzes as $q): ?>
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100 border-start border-4 border-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="badge bg-secondary mb-2"><?= htmlspecialchars($q['course_code']) ?></span>
                                <h5 class="card-title"><?= htmlspecialchars($q['title']) ?></h5>
                                <p class="card-text text-muted small"><?= htmlspecialchars($q['course_title']) ?></p>
                            </div>
                            <?php if($q['my_score'] !== null): ?>
                                <div class="text-end">
                                    <span class="badge bg-success">Completed</span>
                                    <div class="h4 mt-1"><?= number_format($q['my_score'], 1) ?>%</div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3">
                            <?php if($q['my_score'] === null): ?>
                                <a href="take_quiz.php?id=<?= $q['id'] ?>" class="btn btn-primary w-100">
                                    <i class="bi bi-pencil-square"></i> Attempt Quiz
                                </a>
                            <?php else: ?>
                                <button class="btn btn-outline-secondary w-100" disabled>
                                    Already Attempted
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if(empty($quizzes)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center p-5">
                        <i class="bi bi-info-circle display-4 mb-3 d-block"></i>
                        <h5>No quizzes found.</h5>
                        <p>You are not currently enrolled in any courses with active quizzes.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
