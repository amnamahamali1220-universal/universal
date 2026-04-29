<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkRole(['course_instructor', 'teacher', 'admin']);
require_once '../../core/db.php';
require_once '../../includes/header.php';

$course_id = $_GET['course_id'] ?? 0;
$teacher_id = $_SESSION['user_id'];
$date = $_GET['date'] ?? date('Y-m-d');

// Verify Ownership
if ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$course_id, $teacher_id]);
}
$course = $stmt->fetch();

if (!$course) {
    echo '<div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Attendance</h3></div></div></div></div>';
    echo '<div class="app-content"><div class="container-fluid"><div class="alert alert-danger">Please select a valid course from your dashboard first.</div><a href="instructor_dash.php" class="btn btn-primary">Go to Dashboard</a></div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Handle Attendance Submission
if (isset($_POST['save_attendance'])) {
    foreach ($_POST['attendance'] as $student_id => $status) {
        $stmt = $pdo->prepare("INSERT INTO attendance (course_id, student_id, date, status) 
                               VALUES (?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE status = ?");
        $stmt->execute([$course_id, $student_id, $date, $status, $status]);
    }
    $success = "Attendance saved for " . $date;
}

// Fetch Students enrolled in this course
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.registration_no, a.status 
    FROM enrollments e 
    JOIN users u ON e.student_id = u.id 
    LEFT JOIN attendance a ON u.id = a.student_id AND a.course_id = e.course_id AND a.date = ?
    WHERE e.course_id = ?
");
$stmt->execute([$date, $course_id]);
$students = $stmt->fetchAll();

// Attendance Stats for Chart
$p_count = 0; $a_count = 0; $l_count = 0;
foreach($students as $s) {
    if($s['status'] === 'present') $p_count++;
    elseif($s['status'] === 'absent') $a_count++;
    elseif($s['status'] === 'late') $l_count++;
}

require_once '../../includes/visual_helper.php';
echo getChartScripts();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Attendance: <?= htmlspecialchars($course['title']) ?></h3>
            </div>
            <div class="col-sm-6 text-end">
                <a href="instructor_dash.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-success mb-4">
            <div class="card-header">
                <form class="row g-3 align-items-center">
                    <input type="hidden" name="course_id" value="<?= $course_id ?>">
                    <div class="col-auto">
                        <label class="form-label mb-0">Select Date:</label>
                    </div>
                    <div class="col-auto">
                        <input type="date" name="date" class="form-control" value="<?= $date ?>" onchange="this.form.submit()">
                    </div>
                </form>
            </div>
            <div class="card-body">
                <?php if(isset($success)): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>
                
                <form method="post">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Reg No</th>
                                <th>Student Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($students as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['registration_no']) ?></td>
                                <td><?= htmlspecialchars($s['name']) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <input type="radio" class="btn-check" name="attendance[<?= $s['id'] ?>]" id="p_<?= $s['id'] ?>" value="present" <?= $s['status'] === 'present' ? 'checked' : '' ?> required>
                                        <label class="btn btn-outline-success btn-sm" for="p_<?= $s['id'] ?>">Present</label>

                                        <input type="radio" class="btn-check" name="attendance[<?= $s['id'] ?>]" id="a_<?= $s['id'] ?>" value="absent" <?= $s['status'] === 'absent' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-danger btn-sm" for="a_<?= $s['id'] ?>">Absent</label>

                                        <input type="radio" class="btn-check" name="attendance[<?= $s['id'] ?>]" id="l_<?= $s['id'] ?>" value="late" <?= $s['status'] === 'late' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-warning btn-sm" for="l_<?= $s['id'] ?>">Late</label>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($students)): ?>
                                <tr><td colspan="3" class="text-center">No students enrolled in this course.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php if(!empty($students)): ?>
                        <button type="submit" name="save_attendance" class="btn btn-success d-block w-100">Save Attendance</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Attendance Analytics Section -->
        <?php if(!empty($students)): ?>
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-pie me-2"></i> Attendance Summary (<?= $date ?>)</h3>
                    </div>
                    <div class="card-body">
                        <div style="max-height: 250px;">
                            <canvas id="attendanceChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Present', 'Absent', 'Late', 'Unmarked'],
            datasets: [{
                data: [
                    <?= $p_count ?>, 
                    <?= $a_count ?>, 
                    <?= $l_count ?>, 
                    <?= count($students) - ($p_count + $a_count + $l_count) ?>
                ],
                backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#6c757d'],
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
