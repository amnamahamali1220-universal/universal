<?php
require_once '../../core/session.php';
checkRole('teacher');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$teacher_id = $_SESSION['user_id'];

// Get courses
$courses = $pdo->prepare("SELECT id, title FROM courses WHERE teacher_id = ?");
$courses->execute([$teacher_id]);
$my_courses = $courses->fetchAll();

$selected_course = $_GET['course_id'] ?? ($my_courses[0]['id'] ?? 0);

if ($selected_course) {
    // Get Student Performance (Average Grade)
    $sql = "SELECT u.name, u.registration_no, AVG(s.grade) as avg_grade 
            FROM enrollments e 
            JOIN users u ON e.student_id = u.id 
            LEFT JOIN submissions s ON u.id = s.student_id 
            WHERE e.course_id = ? 
            GROUP BY u.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$selected_course]);
    $report_data = $stmt->fetchAll();
}
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Student Performance Report</h3></div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <form method="get" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <select name="course_id" class="form-control" onchange="this.form.submit()">
                        <?php foreach($my_courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $selected_course == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['title']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <?php if($selected_course && !empty($report_data)): ?>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead><tr><th>Student</th><th>Reg No</th><th>Average Score</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach($report_data as $r): 
                            $avg = round($r['avg_grade'], 2);
                            $status = $avg >= 50 ? '<span class="text-success">Pass</span>' : '<span class="text-danger">Fail</span>';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($r['name']) ?></td>
                            <td><?= htmlspecialchars($r['registration_no']) ?></td>
                            <td><?= $avg ?></td>
                            <td><?= $status ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
            <p>No data available using the selected criteria.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
