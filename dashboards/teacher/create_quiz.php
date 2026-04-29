<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkRole(['exam_controller', 'teacher', 'admin']);
require_once '../../core/db.php';
require_once '../../includes/header.php';

$teacher_id = $_SESSION['user_id'];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];

    $stmt = $pdo->prepare("INSERT INTO quizzes (course_id, title) VALUES (?, ?)");
    if ($stmt->execute([$course_id, $title])) {
        $quiz_id = $pdo->lastInsertId();
        header("Location: edit_quiz.php?id=" . $quiz_id);
        exit;
    } else {
        $error = "Failed to create quiz.";
    }
}

// Fetch courses assigned to this teacher
$stmt = $pdo->prepare("SELECT id, title, course_code FROM courses WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$courses = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Create New Quiz</h3></div>
            <div class="col-sm-6 text-end">
                <a href="exam_dash.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-body">
                <?php if(isset($error)): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Select Course</label>
                        <select name="course_id" class="form-select" required>
                            <?php foreach($courses as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['course_code'] . ' - ' . $c['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quiz Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Midterm Quiz 1" required>
                    </div>
                    <button type="submit" class="btn btn-primary d-block w-100">Create & Add Questions</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
