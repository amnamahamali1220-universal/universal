<?php
require_once '../../core/session.php';
checkRole('student');
require_once '../../core/db.php';
require_once '../../includes/visual_helper.php';
require_once '../../includes/header.php';

$student_id = $_SESSION['user_id'];

// 1. Monthly Attendance (Last 6 Months)
$monthly_stmt = $pdo->prepare("
    SELECT DATE_FORMAT(date, '%b %Y') as month, 
           COUNT(*) as total_days,
           SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
    FROM attendance 
    WHERE student_id = ? 
    GROUP BY month 
    ORDER BY date ASC 
    LIMIT 6
");
$monthly_stmt->execute([$student_id]);
$months_data = $monthly_stmt->fetchAll();
$month_labels = [];
$month_counts = [];
foreach ($months_data as $row) {
    $month_labels[] = $row['month'];
    $month_counts[] = $row['present_days'];
}

// 2. Attendance Percentage Threshold
$total_att = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE student_id = ?");
$total_att->execute([$student_id]);
$total_days = $total_att->fetchColumn();

$present_att = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE student_id = ? AND status = 'present'");
$present_att->execute([$student_id]);
$present_days = $present_att->fetchColumn();

$percentage = $total_days > 0 ? ($present_days / $total_days) * 100 : 0;
$alert_class = $percentage < 75 ? 'text-danger' : 'text-success';
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Attendance Analytics</h3>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Overall Percentage Indicator -->
            <div class="col-md-4">
                <div class="card card-outline card-primary shadow-sm text-center p-4">
                    <h5 class="text-muted">Global Attendance</h5>
                    <div class="display-1 font-weight-bold <?= $alert_class ?>">
                        <?= round($percentage, 1) ?>%
                    </div>
                    <?php if ($percentage < 75): ?>
                        <div class="alert alert-danger mt-3">
                            <i class="bi bi-exclamation-triangle-fill"></i> Warning: Attendance is below 75% threshold!
                        </div>
                    <?php else: ?>
                        <div class="text-success mt-2">
                            <i class="bi bi-check-circle-fill"></i> Good Standing
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Monthly Graph -->
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header"><h3 class="card-title">Monthly Attendance Trend (Present Days)</h3></div>
                    <div class="card-body">
                        <canvas id="monthlyAttendanceChart" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= getChartScripts() ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new Chart(document.getElementById('monthlyAttendanceChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($month_labels) ?>,
            datasets: [{
                label: 'Present Days',
                data: <?= json_encode($month_counts) ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
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
