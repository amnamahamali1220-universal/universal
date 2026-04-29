<?php
require_once '../../core/session.php';
checkRole('admin');
require_once '../../core/db.php';
require_once '../../includes/visual_helper.php';
require_once '../../includes/header.php';

// 1. Grade Distribution (All Students, All Courses)
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
    FROM submissions 
    WHERE grade IS NOT NULL
    GROUP BY grade_group
    ORDER BY grade_group ASC
");
$marks_stmt->execute();
$grade_dist = $marks_stmt->fetchAll();
$grade_labels = ['A', 'B', 'C', 'D', 'F'];
$grade_counts = [0, 0, 0, 0, 0];
foreach ($grade_dist as $row) {
    $idx = array_search($row['grade_group'], $grade_labels);
    if ($idx !== false) $grade_counts[$idx] = $row['count'];
}

// 2. Summary Stats
$total_students = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_courses = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$avg_grade = $pdo->query("SELECT AVG(grade) FROM submissions WHERE grade IS NOT NULL")->fetchColumn();

echo getChartScripts();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Grade Distribution Analysis</h3>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Stats -->
            <div class="col-md-4">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon text-bg-primary shadow-sm"><i class="bi bi-people-fill"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Students</span>
                        <span class="info-box-number"><?= $total_students ?></span>
                    </div>
                </div>
                <div class="info-box shadow-sm">
                    <span class="info-box-icon text-bg-success shadow-sm"><i class="bi bi-book"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Courses</span>
                        <span class="info-box-number"><?= $total_courses ?></span>
                    </div>
                </div>
                <div class="info-box shadow-sm">
                    <span class="info-box-icon text-bg-info shadow-sm"><i class="bi bi-graph-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">System Avg Grade</span>
                        <span class="info-box-number"><?= number_format($avg_grade, 2) ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Grade Chart -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Grade Distribution (System-wide)</h3></div>
                    <div class="card-body">
                        <canvas id="adminGradeChart" style="max-height: 350px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('adminGradeChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($grade_labels) ?>,
            datasets: [{
                label: 'Number of Students',
                data: <?= json_encode($grade_counts) ?>,
                backgroundColor: [
                    '#28a745', // A
                    '#17a2b8', // B
                    '#ffc107', // C
                    '#fd7e14', // D
                    '#dc3545'  // F
                ]
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
