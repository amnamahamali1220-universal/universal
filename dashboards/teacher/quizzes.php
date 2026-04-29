<?php
require_once '../../core/session.php';
checkRole('teacher');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$teacher_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? 0;

// Fetch Teacher's Courses
$course_stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ?");
$course_stmt->execute([$teacher_id]);
$my_courses = $course_stmt->fetchAll();

// If no course selected, and teacher has courses, default to first one or show selector
if ($course_id == 0 && !empty($my_courses)) {
    // We'll show a selection UI if not redirected from course_view
}

// Create Quiz
if (isset($_POST['create_quiz']) && $course_id > 0) {
    $title = $_POST['title'];
    $stmt = $pdo->prepare("INSERT INTO quizzes (course_id, title) VALUES (?, ?)");
    $stmt->execute([$course_id, $title]);
    $quiz_id = $pdo->lastInsertId();
    echo "<script>window.location.href='edit_quiz.php?id=$quiz_id&course_id=$course_id';</script>";
    exit;
}

// Fetch Quizzes
$quizzes = [];
if ($course_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $quizzes = $stmt->fetchAll();
}
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Manage Quizzes</h3></div>
            <div class="col-sm-6 text-end">
                <?php if($course_id > 0): ?>
                    <a href="course_view.php?id=<?= $course_id ?>" class="btn btn-secondary">Back to Course</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <div class="card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3 align-items-center">
                    <div class="col-auto">
                        <label class="form-label">Select Course:</label>
                    </div>
                    <div class="col-auto">
                        <select name="course_id" class="form-select" onchange="this.form.submit()">
                            <option value="0">-- Choose Course --</option>
                            <?php foreach($my_courses as $mc): ?>
                                <option value="<?= $mc['id'] ?>" <?= $course_id == $mc['id'] ? 'selected' : '' ?>><?= htmlspecialchars($mc['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <?php if($course_id > 0): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">Create New Quiz</div>
                <div class="card-body">
                    <form method="post" class="row g-2">
                        <div class="col-md-10">
                            <input type="text" name="title" class="form-control" placeholder="Enter Quiz Title" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" name="create_quiz" class="btn btn-primary w-100">Create</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header">Existing Quizzes</div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach($quizzes as $q): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-journal-text me-2"></i>
                                <strong><?= htmlspecialchars($q['title']) ?></strong>
                            </div>
                            <div>
                                <a href="edit_quiz.php?id=<?= $q['id'] ?>&course_id=<?= $course_id ?>" class="btn btn-sm btn-info"><i class="bi bi-pencil"></i> Edit</a>
                                <a href="quiz_results.php?id=<?= $q['id'] ?>&course_id=<?= $course_id ?>" class="btn btn-sm btn-success"><i class="bi bi-list-check"></i> Results</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($quizzes)): ?>
                            <div class="p-4 text-center text-muted">No quizzes found for this course.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Please select a course to manage quizzes.</div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
