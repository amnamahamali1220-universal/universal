<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkTeacherSubRole(ROLE_ASSIGNMENT);
require_once '../../includes/header.php';

$teacher_id = $_SESSION['user_id'];
// Fetch assignments managed by this teacher (or all if senior)
$stmt = $pdo->prepare("SELECT a.*, c.title as course_title FROM assignments a JOIN courses c ON a.course_id = c.id WHERE c.teacher_id = ?");
$stmt->execute([$teacher_id]);
$assignments = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3 class="mb-0">Assignment Manager Dashboard</h3></div>
            <div class="col-sm-6 text-end">
                <a href="create_assignment.php" class="btn btn-primary">Create New Assignment</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3><?= count($assignments) ?></h3>
                        <p>Total Assignments</p>
                    </div>
                    <div class="icon"><i class="bi bi-file-earmark-plus"></i></div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header"><h3 class="card-title">Manage Assignments</h3></div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Assignment Title</th>
                            <th>Course</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($assignments as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['title']) ?></td>
                            <td><?= htmlspecialchars($a['course_title']) ?></td>
                            <td><?= htmlspecialchars($a['due_date']) ?></td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="view_submissions.php?assignment_id=<?= $a['id'] ?>" class="btn btn-sm btn-info">View Submissions</a>
                                    <a href="grade_assignment.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-success">Grade</a>
                                    <a href="edit_assignment.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($assignments)): ?>
                        <tr><td colspan="4" class="text-center">No assignments created yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
