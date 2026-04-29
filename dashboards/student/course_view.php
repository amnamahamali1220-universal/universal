<?php
require_once '../../core/session.php';
checkRole('student');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$student_id = $_SESSION['user_id'];
$course_id = $_GET['id'] ?? 0;

// Verify Enrollment
$stmt = $pdo->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
$stmt->execute([$student_id, $course_id]);
if ($stmt->rowCount() == 0) {
    echo "<div class='p-4'><h3>Access Denied. You are not enrolled in this course.</h3></div>";
    require_once '../../includes/footer.php';
    exit;
}

// Fetch Course Info
$course_stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$course_stmt->execute([$course_id]);
$course = $course_stmt->fetch();

// Handle Assignment Submission
if (isset($_POST['submit_assignment'])) {
    $a_id = $_POST['assignment_id'];
    
    // File Upload
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
        $target_dir = "../../uploads/submissions/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . '_' . $student_id . '_' . basename($_FILES["submission_file"]["name"]);
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["submission_file"]["tmp_name"], $target_file)) {
            $file_path = "uploads/submissions/" . $file_name;
            
            // Check if already submitted (update or insert)
            $check = $pdo->prepare("SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ?");
            $check->execute([$a_id, $student_id]);
            if ($check->rowCount() > 0) {
                // Update
                $up = $pdo->prepare("UPDATE submissions SET file_path = ?, submitted_at = CURRENT_TIMESTAMP WHERE assignment_id = ? AND student_id = ?");
                $up->execute([$file_path, $a_id, $student_id]);
            } else {
                // Insert
                $in = $pdo->prepare("INSERT INTO submissions (assignment_id, student_id, file_path) VALUES (?, ?, ?)");
                $in->execute([$a_id, $student_id, $file_path]);
            }
            echo "<script>alert('Assignment Submitted Successfully!');</script>";
        }
    }
}

// Fetch Content
$materials = $pdo->prepare("SELECT m.*, v.id as viewed 
                            FROM materials m 
                            LEFT JOIN material_views v ON m.id = v.material_id AND v.student_id = ? 
                            WHERE m.course_id = ? ORDER BY m.uploaded_at DESC");
$materials->execute([$student_id, $course_id]);
$mat_list = $materials->fetchAll();

$assignments = $pdo->prepare("SELECT a.*, s.file_path as submission_path, s.grade, s.feedback 
                              FROM assignments a 
                              LEFT JOIN submissions s ON a.id = s.assignment_id AND s.student_id = ? 
                              WHERE a.course_id = ?");
$assignments->execute([$student_id, $course_id]);
$assign_list = $assignments->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3><?= htmlspecialchars($course['title']) ?></h3>
            </div>
            <div class="col-sm-6 text-end">
                <a href="my_learning.php" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <ul class="nav nav-tabs" id="stuTabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#mat">Materials</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#assign">Assignments</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#quizzes">Quizzes</a></li>
        </ul>

        <div class="tab-content p-3 border border-top-0 bg-white">
            <!-- Materials -->
            <div class="tab-pane fade show active" id="mat">
                <div class="list-group">
                    <?php foreach($mat_list as $m): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-file-earmark-text"></i> <?= htmlspecialchars($m['title']) ?>
                                <small class="text-muted ms-2">(<?= $m['type'] ?>)</small>
                                <?php if($m['viewed']): ?>
                                    <span class="badge bg-success ms-2"><i class="bi bi-check-circle"></i> Completed</span>
                                <?php endif; ?>
                            </div>
                            <?php if($m['file_path']): ?>
                                <a href="action_view_material.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">Download / View</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Assignments -->
            <div class="tab-pane fade" id="assign">
                <div class="accordion" id="assignAccordion">
                    <?php foreach($assign_list as $index => $a): ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>">
                                    <?= htmlspecialchars($a['title']) ?> 
                                    <?php if($a['submission_path']): ?>
                                        <span class="badge bg-success ms-2">Submitted</span>
                                    <?php elseif(strtotime($a['due_date']) < time()): ?>
                                        <span class="badge bg-danger ms-2">Late</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning ms-2">Pending</span>
                                    <?php endif; ?>
                                </button>
                            </h2>
                            <div id="collapse<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#assignAccordion">
                                <div class="accordion-body">
                                    <p><?= htmlspecialchars($a['description']) ?></p>
                                    <p><strong>Due Date:</strong> <?= $a['due_date'] ?></p>
                                    
                                    <?php if($a['submission_path']): ?>
                                        <div class="alert alert-success">
                                            You have submitted this assignment. 
                                            <a href="<?= BASE_URL . $a['submission_path'] ?>" target="_blank">View Submission</a>
                                            <?php if($a['grade']): ?>
                                                <hr>
                                                <strong>Grade:</strong> <?= $a['grade'] ?><br>
                                                <strong>Feedback:</strong> <?= htmlspecialchars($a['feedback']) ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-3">
                                        <a href="submit_assignment.php?assignment_id=<?= $a['id'] ?>" class="btn btn-primary rounded-pill">
                                            <i class="fas fa-paper-plane me-1"></i> Go to Submission Page
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quizzes -->
            <div class="tab-pane fade" id="quizzes">
                <div class="list-group">
                    <?php 
                    $stmt = $pdo->prepare("SELECT q.*, (SELECT score FROM quiz_attempts WHERE quiz_id = q.id AND student_id = ?) as my_score 
                                            FROM quizzes q WHERE q.course_id = ?");
                    $stmt->execute([$student_id, $course_id]);
                    $course_quizzes = $stmt->fetchAll();
                    foreach($course_quizzes as $cq): 
                    ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-question-circle"></i> <?= htmlspecialchars($cq['title']) ?>
                                <?php if($cq['my_score'] !== null): ?>
                                    <span class="badge bg-success ms-2">Score: <?= number_format($cq['my_score'], 1) ?>%</span>
                                <?php endif; ?>
                            </div>
                            <?php if($cq['my_score'] === null): ?>
                                <a href="take_quiz.php?id=<?= $cq['id'] ?>" class="btn btn-sm btn-primary">Take Quiz</a>
                            <?php else: ?>
                                <span class="text-muted small">Completed</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($course_quizzes)) echo "<p class='text-muted'>No quizzes available for this course.</p>"; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
