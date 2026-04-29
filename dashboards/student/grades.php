<?php
require_once '../../core/session.php';
checkRole('student');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$student_id = $_SESSION['user_id'];

// Fetch All Grades
$sql = "SELECT s.*, a.title as assignment_title, c.title as course_title, c.course_code 
        FROM submissions s 
        JOIN assignments a ON s.assignment_id = a.id 
        JOIN courses c ON a.course_id = c.id 
        WHERE s.student_id = ? 
        ORDER BY s.submitted_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id]);
$grades = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>My Grades</h3></div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Course</th>
                            <th>Assignment</th>
                            <th>Submitted</th>
                            <th>Grade</th>
                            <th>Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($grades as $g): ?>
                        <tr>
                            <td><?= htmlspecialchars($g['course_code']) ?></td>
                            <td><?= htmlspecialchars($g['assignment_title']) ?></td>
                            <td><?= $g['submitted_at'] ?></td>
                            <td>
                                <?php if($g['grade'] !== null): ?>
                                    <span class="badge bg-success"><?= $g['grade'] ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($g['feedback']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($grades)) echo "<tr><td colspan='5'>No grades available.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
