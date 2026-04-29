<?php
require_once '../../core/session.php';
checkRole('teacher');
require_once '../../core/db.php';
require_once '../../includes/header.php';
$teacher_id = $_SESSION['user_id'];
$assignment_id = $_GET['id'] ?? 0;

// Verify Ownership
$stmt = $pdo->prepare("SELECT a.*, c.title as course_title FROM assignments a JOIN courses c ON a.course_id = c.id WHERE a.id = ? AND c.teacher_id = ?");
$stmt->execute([$assignment_id, $teacher_id]);
$assignment = $stmt->fetch();

if (!$assignment) {
    die("Access Denied or Assignment Not Found");
}

// Handle Grading
if (isset($_POST['save_grade'])) {
    $sub_id = $_POST['submission_id'];
    $grade = $_POST['grade'];
    $feedback = $_POST['feedback'];
    
    $up = $pdo->prepare("UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?");
    $up->execute([$grade, $feedback, $sub_id]);
    echo "<script>alert('Grade Saved');</script>";
}

// Fetch Submissions
$sql = "SELECT s.*, u.name as student_name, u.registration_no 
        FROM submissions s 
        JOIN users u ON s.student_id = u.id 
        WHERE s.assignment_id = ?";
$subs = $pdo->prepare($sql);
$subs->execute([$assignment_id]);
$submissions = $subs->fetchAll();

// Check if a rubric exists for this assignment
$rStmt = $pdo->prepare("SELECT id FROM rubrics WHERE assignment_id = ?");
$rStmt->execute([$assignment_id]);
$has_rubric = $rStmt->rowCount() > 0;
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3>Grading: <?= htmlspecialchars($assignment['title']) ?></h3>
                <small><?= htmlspecialchars($assignment['course_title']) ?></small>
            </div>
            <div class="col-sm-6 text-end">
                <a href="course_view.php?id=<?= $assignment['course_id'] ?>" class="btn btn-secondary">Back to Course</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Reg No</th>
                            <th>Submitted At</th>
                            <th>File</th>
                            <th>Grade / Feedback</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($submissions as $sub): ?>
                        <tr>
                            <td><?= htmlspecialchars($sub['student_name']) ?></td>
                            <td><?= htmlspecialchars($sub['registration_no']) ?></td>
                            <td><?= $sub['submitted_at'] ?></td>
                            <td><a href="<?= BASE_URL . $sub['file_path'] ?>" target="_blank">View File</a></td>
                            <form method="post">
                                <td>
                                    <input type="number" name="grade" class="form-control mb-1" placeholder="Score" value="<?= $sub['grade'] ?>" step="0.1">
                                    <textarea name="feedback" class="form-control" placeholder="Feedback"><?= htmlspecialchars($sub['feedback']) ?></textarea>
                                    <input type="hidden" name="submission_id" value="<?= $sub['id'] ?>">
                                </td>
                                <td>
                                    <button type="submit" name="save_grade" class="btn btn-sm btn-primary d-block mb-2 w-100">Save</button>
                                    <?php if($has_rubric): ?>
                                    <a href="grade_with_rubric.php?submission_id=<?= $sub['id'] ?>" class="btn btn-sm btn-info d-block w-100 text-white"><i class="fas fa-list-check me-1"></i> Use Rubric</a>
                                    <?php endif; ?>
                                </td>
                            </form>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($submissions)) echo "<tr><td colspan='6' class='text-center'>No submissions yet.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
