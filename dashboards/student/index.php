<?php
require_once '../../core/session.php';
checkRole('student');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$student_id = $_SESSION['user_id'];

// Get enrolled courses count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE student_id = ?");
$stmt->execute([$student_id]);
$enrolled_count = $stmt->fetchColumn();

?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Student Dashboard</h3>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3><?= $enrolled_count ?></h3>
                        <p>My Courses</p>
                    </div>
                    <a href="my_learning.php" class="small-box-footer">Go to Learning <i class="bi bi-arrow-right-circle"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <!-- small box -->
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>Report</h3>
                        <p>Academic Results</p>
                    </div>
                    <a href="report_card.php" class="small-box-footer">Download Report Card <i class="bi bi-download"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
