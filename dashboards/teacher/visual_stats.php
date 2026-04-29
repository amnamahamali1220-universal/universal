<?php
require_once '../../core/session.php';
checkRole('teacher');
require_once '../../core/db.php';
require_once '../../includes/visual_helper.php';
require_once '../../includes/header.php';

$teacher_id = $_SESSION['user_id'];

// 1. Students per Course
$enroll_stmt = $pdo->prepare("
    SELECT c.title, COUNT(e.id) as student_count 
    FROM courses c 
    LEFT JOIN enrollments e ON c.id = e.course_id 
    WHERE c.teacher_id = ? 
    GROUP BY c.id
");
$enroll_stmt->execute([$teacher_id]);
$enroll_data = $enroll_stmt->fetchAll();
$course_labels = [];
$course_counts = [];
foreach ($enroll_data as $row) {
    $course_labels[] = $row['title'];
    $course_counts[] = $row['student_count'];
}

// 2. Submitted vs Pending Assignments (Across all teacher's courses)
$assign_stmt = $pdo->prepare("
    SELECT 
        (SELECT COUNT(*) FROM assignments a JOIN enrollments e ON a.course_id = e.course_id WHERE a.course_id IN (SELECT id FROM courses WHERE teacher_id = ?)) as total_expected,
        (SELECT COUNT(*) FROM submissions s JOIN assignments a ON s.assignment_id = a.id WHERE a.course_id IN (SELECT id FROM courses WHERE teacher_id = ?)) as total_submitted
");
$assign_stmt->execute([$teacher_id, $teacher_id]);
$assign_stats = $assign_stmt->fetch();
$total_pending = max(0, $assign_stats['total_expected'] - $assign_stats['total_submitted']);

// 3. Marks Distribution (Histogram style)
$marks_stmt = $pdo->prepare("
    SELECT 
        CASE 
            WHEN grade >= 90 THEN 'A'
            WHEN grade >= 80 THEN 'B'
            WHEN grade >= 70 THEN 'C'
            WHEN grade >= 60 THEN 'D'
            ELSE 'F'
        END as grade_group,
        COUNT(*) as count
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.id
    WHERE a.course_id IN (SELECT id FROM courses WHERE teacher_id = ?) AND s.grade IS NOT NULL
    GROUP BY grade_group
    ORDER BY grade_group ASC
");
$marks_stmt->execute([$teacher_id]);
$grade_dist = $marks_stmt->fetchAll();
$grade_labels = ['A', 'B', 'C', 'D', 'F'];
$grade_counts = [0, 0, 0, 0, 0];
foreach ($grade_dist as $row) {
    $idx = array_search($row['grade_group'], $grade_labels);
    if ($idx !== false) $grade_counts[$idx] = $row['count'];
}

// 4. Teacher Activity Summary Cards
$total_courses = count($enroll_data);
$total_students = array_sum($course_counts);
$total_assignments = $pdo->prepare("SELECT COUNT(*) FROM assignments WHERE course_id IN (SELECT id FROM courses WHERE teacher_id = ?)");
$total_assignments->execute([$teacher_id]);
$assign_count = $total_assignments->fetchColumn();

echo getChartScripts();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Teacher Analytics</h3>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Activity Cards -->
        <div class="row">
            <div class="col-lg-4 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3><?= $total_courses ?></h3>
                        <p>Total Courses</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3><?= $total_students ?></h3>
                        <p>Total Students Enrolled</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-12">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3><?= $assign_count ?></h3>
                        <p>Assignments Created</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Students per Course -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">Enrollment per Course</h3></div>
                    <div class="card-body">
                        <canvas id="enrollChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Submission Status -->
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">Global Submission Status</h3></div>
                    <div class="card-body">
                        <div style="max-width: 300px; margin: auto;">
                            <canvas id="submissionPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Marks Distribution -->
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header"><h3 class="card-title">Overall Marks Distribution</h3></div>
                    <div class="card-body">
                        <canvas id="marksDistChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enrollment Bar Chart
    new Chart(document.getElementById('enrollChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($course_labels) ?>,
            datasets: [{
                label: 'Students',
                data: <?= json_encode($course_counts) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    // Submission Pie Chart
    new Chart(document.getElementById('submissionPieChart'), {
        type: 'doughnut',
        data: {
            labels: ['Submitted', 'Pending'],
            datasets: [{
                data: [<?= $assign_stats['total_submitted'] ?>, <?= $total_pending ?>],
                backgroundColor: ['#28a745', '#ffc107']
            }]
        }
    });

    // Marks Distribution Chart
    new Chart(document.getElementById('marksDistChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($grade_labels) ?>,
            datasets: [{
                label: 'Count of Students',
                data: <?= json_encode($grade_counts) ?>,
                backgroundColor: <?= json_encode(getChartColors(5)) ?>
            }]
        },
        options: { 
            responsive: true, 
            scales: { 
                y: { beginAtZero: true, ticks: { stepSize: 1 } } 
            }
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
