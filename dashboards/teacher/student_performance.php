<?php
require_once '../../core/session.php';
checkRole(['teacher', 'course_instructor', 'admin', 'super_admin']);
require_once '../../core/db.php';
require_once '../../includes/header.php';

$student_id = $_GET['id'] ?? 0;

// Fetch Student Profile
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    echo "<div class='p-4'><h3>Student not found.</h3><a href='my_students.php' class='btn btn-primary'>Back</a></div>";
    require_once '../../includes/footer.php';
    exit;
}

// Fetch Courses and Grades
$sql = "SELECT c.id as course_id, c.course_code, c.title as course_title,
            (SELECT AVG(grade) FROM submissions s JOIN assignments a ON s.assignment_id = a.id WHERE a.course_id = c.id AND s.student_id = ?) as avg_assignment,
            (SELECT AVG(score) FROM quiz_attempts qa JOIN quizzes q ON qa.quiz_id = q.id WHERE q.course_id = c.id AND qa.student_id = ?) as avg_quiz
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE e.student_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id, $student_id, $student_id]);
$courses = $stmt->fetchAll();

// GPA Calculation Helper
function getGradeScale($percentage) {
    if ($percentage >= 90) return ['grade' => 'A+', 'point' => 4.0];
    if ($percentage >= 85) return ['grade' => 'A', 'point' => 4.0];
    if ($percentage >= 80) return ['grade' => 'A-', 'point' => 3.7];
    if ($percentage >= 75) return ['grade' => 'B+', 'point' => 3.3];
    if ($percentage >= 70) return ['grade' => 'B', 'point' => 3.0];
    if ($percentage >= 65) return ['grade' => 'B-', 'point' => 2.7];
    if ($percentage >= 60) return ['grade' => 'C+', 'point' => 2.3];
    if ($percentage >= 55) return ['grade' => 'C', 'point' => 2.0];
    if ($percentage >= 50) return ['grade' => 'C-', 'point' => 1.7];
    if ($percentage >= 45) return ['grade' => 'D', 'point' => 1.0];
    return ['grade' => 'F', 'point' => 0.0];
}

$total_points = 0;
$valid_courses = 0;
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Student Performance</h3>
            </div>
            <div class="col-sm-6 text-end">
                <a href="my_students.php" class="btn btn-secondary">Back to My Students</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-primary mb-4">
            <div class="card-header">
                <h3 class="card-title">Profile: <?= htmlspecialchars($student['name']) ?></h3>
            </div>
            <div class="card-body">
                <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
                <p><strong>Registration No:</strong> <?= htmlspecialchars($student['registration_no'] ?: 'N/A') ?></p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Academic Record</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-bordered text-center mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Course</th>
                            <th>Subject</th>
                            <th>Performance (%)</th>
                            <th>Grade</th>
                            <th>GP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($courses as $c): 
                            $avg_ass = $c['avg_assignment'] !== null ? $c['avg_assignment'] : 0;
                            $avg_qui = $c['avg_quiz'] !== null ? $c['avg_quiz'] : 0;
                            
                            if ($c['avg_assignment'] !== null && $c['avg_quiz'] !== null) {
                                $final_percentage = ($avg_ass + $avg_qui) / 2;
                            } else if ($c['avg_assignment'] !== null) {
                                $final_percentage = $avg_ass;
                            } else if ($c['avg_quiz'] !== null) {
                                $final_percentage = $avg_qui;
                            } else {
                                $final_percentage = null;
                            }

                            if ($final_percentage !== null) {
                                $scale = getGradeScale($final_percentage);
                                $total_points += $scale['point'];
                                $valid_courses++;
                                $display_mark = number_format($final_percentage, 1) . '%';
                                $display_grade = $scale['grade'];
                                $display_point = number_format($scale['point'], 1);
                            } else {
                                $display_mark = '-';
                                $display_grade = 'N/A';
                                $display_point = '-';
                            }
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($c['course_code']) ?></strong></td>
                            <td class="text-start"><?= htmlspecialchars($c['course_title']) ?></td>
                            <td><?= $display_mark ?></td>
                            <td class="fw-bold text-primary"><?= $display_grade ?></td>
                            <td><?= $display_point ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <?php if(empty($courses)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">This student is not enrolled in any courses yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-light">
                <?php $final_gpa = ($valid_courses > 0) ? ($total_points / $valid_courses) : 0; ?>
                <h4 class="mb-0 text-end">Overall GPA: <span class="text-success fw-bold"><?= number_format($final_gpa, 2) ?></span></h4>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
