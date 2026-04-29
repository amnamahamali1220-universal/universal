<?php
require_once '../../core/session.php';
checkRole('student');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$student_id = $_SESSION['user_id'];

// Handle Enrollment Action
if (isset($_POST['enroll'])) {
    $course_id = $_POST['course_id'];
    // Check if valid student/course
    // Check if already enrolled
    $check = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ? AND course_id = ?");
    $check->execute([$student_id, $course_id]);
    if ($check->fetchColumn() == 0) {
        $stmt = $pdo->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        $stmt->execute([$student_id, $course_id]);
        echo "<script>alert('Enrolled successfully!'); window.location.href='my_learning.php';</script>";
    } else {
        echo "<script>alert('Already enrolled!');</script>";
    }
}

// Fetch All Courses NOT enrolled in
// Note: This query excludes courses the student is already taking.
$sql = "SELECT c.*, u.name as teacher_name 
        FROM courses c 
        JOIN users u ON c.teacher_id = u.id 
        WHERE c.id NOT IN (SELECT course_id FROM enrollments WHERE student_id = ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id]);
$available_courses = $stmt->fetchAll();

?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Browse Courses</h3></div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <?php foreach($available_courses as $c): ?>
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($c['title']) ?> (<?= htmlspecialchars($c['course_code']) ?>)</h5>
                        <p class="card-text"><?= htmlspecialchars($c['description']) ?></p>
                        <p class="text-muted">Instructor: <?= htmlspecialchars($c['teacher_name']) ?></p>
                        <form method="post">
                            <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                            <button type="submit" name="enroll" class="btn btn-success">Enroll Now</button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($available_courses)) echo "<p>No new courses available.</p>"; ?>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
