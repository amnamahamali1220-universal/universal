<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkRole(['assignment_manager', 'teacher', 'admin']);
require_once '../../core/db.php';
require_once '../../includes/header.php';

$id = $_GET['id'] ?? 0;
$teacher_id = $_SESSION['user_id'];

// Fetch Assignment
$stmt = $pdo->prepare("SELECT a.*, c.title as course_title FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.id = ? AND c.teacher_id = ?");
$stmt->execute([$id, $teacher_id]);
$assignment = $stmt->fetch();

if (!$assignment) {
    die("Access Denied or Assignment Not Found");
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $due_date = $_POST['due_date'];

    $stmt = $pdo->prepare("UPDATE assignments SET title = ?, description = ?, due_date = ? WHERE id = ?");
    if ($stmt->execute([$title, $description, $due_date, $id])) {
        $success = "Assignment updated successfully!";
        // Refresh data
        $assignment['title'] = $title;
        $assignment['description'] = $description;
        $assignment['due_date'] = $due_date;
    } else {
        $error = "Failed to update assignment.";
    }
}
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Edit Assignment</h3></div>
            <div class="col-sm-6 text-end">
                <a href="assignment_dash.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-warning">
            <div class="card-body">
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($assignment['course_title']) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assignment Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($assignment['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($assignment['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Due Date</label>
                        <?php $dt = date('Y-m-d\TH:i', strtotime($assignment['due_date'])); ?>
                        <input type="datetime-local" name="due_date" class="form-control" value="<?= $dt ?>" required>
                    </div>
                    <button type="submit" class="btn btn-warning d-block w-100">Update Assignment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
