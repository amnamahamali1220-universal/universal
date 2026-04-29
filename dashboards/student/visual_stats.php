<?php
require_once '../../core/session.php';
checkRole('student');
require_once '../../core/db.php';
require_once '../../includes/visual_helper.php';
require_once '../../includes/header.php';

$student_id = $_SESSION['user_id'];

// 1. Attendance Data
$att_stmt = $pdo->prepare("SELECT status, COUNT(*) as count FROM attendance WHERE student_id = ? GROUP BY status");
$att_stmt->execute([$student_id]);
$attendance_data = $att_stmt->fetchAll();
$att_labels = [];
$att_counts = [];
$att_colors = [];
foreach ($attendance_data as $row) {
    $att_labels[] = ucfirst($row['status']);
    $att_counts[] = $row['count'];
    $att_colors[] = getStatusColor($row['status']);
}

// 2. Assignment Data
$assign_stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM assignments a JOIN enrollments e ON a.course_id = e.course_id WHERE e.student_id = ?) as total,
        (SELECT COUNT(*) FROM submissions WHERE student_id = ?) as submitted
");
$assign_stmt->execute([$student_id, $student_id]);
$assign_stats = $assign_stmt->fetch();
$pending = max(0, $assign_stats['total'] - $assign_stats['submitted']);

// 3. Marks Trend (GPA/Marks)
$marks_stmt = $pdo->prepare("
    SELECT c.course_code, s.grade 
    FROM submissions s 
    JOIN assignments a ON s.assignment_id = a.id 
    JOIN courses c ON a.course_id = c.id
    WHERE s.student_id = ? AND s.grade IS NOT NULL
    ORDER BY s.submitted_at ASC
");
$marks_stmt->execute([$student_id]);
$marks_data = $marks_stmt->fetchAll();
$marks_labels = [];
$marks_values = [];
foreach ($marks_data as $row) {
    $marks_labels[] = $row['course_code'];
    $marks_values[] = $row['grade'];
}

// 4. Course Progress (Placeholder logic: Based on assignments submitted vs total in course)
$progress_stmt = $pdo->prepare("
    SELECT c.title, 
           (SELECT COUNT(*) FROM assignments WHERE course_id = c.id) as total_a,
           (SELECT COUNT(*) FROM submissions s JOIN assignments a ON s.assignment_id = a.id WHERE s.student_id = ? AND a.course_id = c.id) as done_a
    FROM courses c
    JOIN enrollments e ON c.id = e.course_id
    WHERE e.student_id = ?
");
$progress_stmt->execute([$student_id, $student_id]);
$course_progress = $progress_stmt->fetchAll();

echo getChartScripts();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Visual Dashboard</h3>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Attendance Chart -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">Attendance Overview</h3></div>
                    <div class="card-body">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Assignment Completion Chart -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">Assignment Completion</h3></div>
                    <div class="card-body">
                        <canvas id="assignmentChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Marks Trend -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">Marks Trend</h3></div>
                    <div class="card-body">
                        <canvas id="marksChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Course Progress -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">Course Progress</h3></div>
                    <div class="card-body">
                        <?php foreach($course_progress as $cp): 
                            $percent = $cp['total_a'] > 0 ? ($cp['done_a'] / $cp['total_a']) * 100 : 0;
                        ?>
                            <div class="mb-3">
                                <strong><?= htmlspecialchars($cp['title']) ?></strong>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $percent ?>%;" aria-valuenow="<?= $percent ?>" aria-valuemin="0" aria-valuemax="100"><?= round($percent) ?>%</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Attendance Bar Chart
    new Chart(document.getElementById('attendanceChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($att_labels) ?>,
            datasets: [{
                label: 'Days',
                data: <?= json_encode($att_counts) ?>,
                backgroundColor: <?= json_encode($att_colors) ?>
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    // 2. Assignment Pie Chart
    new Chart(document.getElementById('assignmentChart'), {
        type: 'pie',
        data: {
            labels: ['Submitted', 'Pending'],
            datasets: [{
                data: [<?= $assign_stats['submitted'] ?>, <?= $pending ?>],
                backgroundColor: ['#28a745', '#ffc107']
            }]
        },
        options: { responsive: true }
    });

    // 3. Marks Line Chart
    new Chart(document.getElementById('marksChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode($marks_labels) ?>,
            datasets: [{
                label: 'Marks/Grade',
                data: <?= json_encode($marks_values) ?>,
                borderColor: '#007bff',
                fill: false,
                tension: 0.1
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
