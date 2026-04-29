<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkRole(['assignment_manager', 'teacher', 'admin']);
require_once '../../core/db.php';
require_once '../../includes/header.php';

$teacher_id = $_SESSION['user_id'];

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    $stmt = $pdo->prepare("INSERT INTO assignments (course_id, title, description, due_date) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$course_id, $title, $description, $due_date])) {
        require_once '../../includes/activity_logger.php';
        logActivity($pdo, $_SESSION['user_id'], "Created assignment: " . $title . " for course_id: " . $course_id);
        
        require_once '../../includes/notification_helper.php';
        notifyCourseStudents($pdo, $course_id, "New Assignment Posted: " . $title, "dashboards/student/course_view.php?id=".$course_id);

        $success = "Assignment created successfully!";
    } else {
        $error = "Failed to create assignment.";
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
            <div class="col-sm-6"><h3 class="mb-0">Create Assignment</h3></div>
            <div class="col-sm-6 text-end">
                <a href="assignment_dash.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-body">
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

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
                        <label class="form-label">Assignment Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <input type="datetime-local" name="due_date" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary d-block w-100">Create Assignment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
