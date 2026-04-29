<?php
require_once '../../core/session.php';
checkRole('student');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$student_id = $_SESSION['user_id'];

// Fetch Enrolled Courses
$stmt = $pdo->prepare("
    SELECT c.id, c.title, c.description, c.course_code, t.name as teacher_name 
    FROM enrollments e 
    JOIN courses c ON e.course_id = c.id 
    JOIN users t ON c.teacher_id = t.id 
    WHERE e.student_id = ?
");
$stmt->execute([$student_id]);
$courses = $stmt->fetchAll();

// Handle Enrollment (Simple version: list all available courses to enroll)
// A real system would have a separate 'Browse Courses' page, but I'll put a simple one here or a link.
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>My Learning</h3></div>
            <div class="col-sm-6 text-end">
                <a href="browse_courses.php" class="btn btn-outline-primary">Browse New Courses</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <?php foreach($courses as $course): ?>
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($course['title']) ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars($course['course_code'] ?? 'N/A') ?></h6>
                        <p class="card-text">Instructor: <?= htmlspecialchars($course['teacher_name']) ?></p>
                        
                        <?php
                            $cid = $course['id'];
                            
                            $c1 = $pdo->query("SELECT COUNT(*) FROM materials WHERE course_id = $cid")->fetchColumn();
                            $c2 = $pdo->query("SELECT COUNT(*) FROM assignments WHERE course_id = $cid")->fetchColumn();
                            $total_items = $c1 + $c2;
                            
                            $d1 = $pdo->query("SELECT COUNT(*) FROM material_views v JOIN materials m ON v.material_id = m.id WHERE m.course_id = $cid AND v.student_id = $student_id")->fetchColumn();
                            $d2 = $pdo->query("SELECT COUNT(*) FROM submissions s JOIN assignments a ON s.assignment_id = a.id WHERE a.course_id = $cid AND s.student_id = $student_id")->fetchColumn();
                            $done_items = $d1 + $d2;
                            
                            $progress = ($total_items > 0) ? round(($done_items / $total_items) * 100) : 0;
                        ?>
                        <div class="progress mt-3 mb-1" style="height: 8px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?= $progress ?>%;"></div>
                        </div>
                        <small class="text-muted d-block mb-3"><?= $progress ?>% Completed (<?= $done_items ?>/<?= $total_items ?> Tasks)</small>
                        
                        <a href="course_view.php?id=<?= $course['id'] ?>" class="btn btn-primary">Go to Class</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($courses)) echo "<p class='text-center w-100'>You are not enrolled in any courses yet.</p>"; ?>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
