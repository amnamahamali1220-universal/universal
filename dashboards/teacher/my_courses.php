<?php
require_once '../../core/session.php';
checkRole('teacher');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$teacher_id = $_SESSION['user_id'];

// Handle Course Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_course'])) {
    $title = $_POST['title'];
    $code = $_POST['course_code']; // Ensure this column exists in SQL
    $desc = $_POST['description'];
    
    $stmt = $pdo->prepare("INSERT INTO courses (teacher_id, title, course_code, description) VALUES (?, ?, ?, ?)");
    $stmt->execute([$teacher_id, $title, $code, $desc]);
    echo "<script>alert('Course Created!'); window.location.href='my_courses.php';</script>";
}

// Fetch Courses
$stmt = $pdo->prepare("SELECT * FROM courses WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$courses = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>My Courses</h3></div>
            <div class="col-sm-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">Create New Course</button>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <?php foreach($courses as $course): ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                    </div>
                    <div class="card-body">
                        <p><?= htmlspecialchars($course['description']) ?></p>
                        <a href="course_view.php?id=<?= $course['id'] ?>" class="btn btn-sm btn-info">Manage Course</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="createCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Course</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Course Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Course Code</label>
                    <input type="text" name="course_code" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <input type="hidden" name="create_course" value="1">
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Create</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
