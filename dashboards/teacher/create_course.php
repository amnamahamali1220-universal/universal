<?php
require_once '../../core/session.php';
checkRole('teacher');
require_once '../../core/db.php';
require_once '../../includes/header.php';


$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $code = trim($_POST['course_code']);
    $desc = trim($_POST['description']);
$teacherId = $_SESSION['user_id'];

    if ($title && $desc) {
        $stmt = $pdo->prepare("INSERT INTO courses (teacher_id, title, course_code, description) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$teacherId, $title, $code, $desc])) {
            echo "<script>window.location.href='my_courses.php';</script>";
            exit;
        } else {
            $msg = "Error creating course.";
        }
    } else {
        $msg = "Please fill in all fields.";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Create New Course</h3>
            </div>
            <form method="post">
                <div class="card-body">
                    <?php if($msg): ?>
                        <div class="alert alert-danger"><?= $msg ?></div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label>Course Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g. Introduction to Computer Science">
                    </div>

                    <div class="mb-3">
                        <label>Course Code</label>
                        <input type="text" name="course_code" class="form-control" placeholder="e.g. CS101">
                    </div>
                    
                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="description" class="form-control" rows="5" required placeholder="Course objectives and details..."></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">Create Course</button>
                    <a href="my_courses.php" class="btn btn-default float-end">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
