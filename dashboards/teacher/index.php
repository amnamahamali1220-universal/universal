<?php
require_once '../../core/session.php';
checkRole('teacher');
require_once '../../core/db.php';
require_once '../../includes/header.php';

// Fetch stats for dashboard
$teacher_id = $_SESSION['user_id'];
$course_count = $pdo->query("SELECT COUNT(*) FROM courses WHERE teacher_id = $teacher_id")->fetchColumn();
$total_students = $pdo->query("SELECT COUNT(*) FROM teacher_student_assign WHERE teacher_id = $teacher_id")->fetchColumn();

// Teacher Specific Analytics
// 1. Student Enrollment per Course
$enrollment_stats = $pdo->query("SELECT title, (SELECT COUNT(*) FROM enrollments WHERE course_id = courses.id) as student_count FROM courses WHERE teacher_id = $teacher_id")->fetchAll();

// 2. Assignment Submission rate
$submission_stats = $pdo->query("SELECT a.title, (SELECT COUNT(*) FROM submissions s WHERE s.assignment_id = a.id) as submissions, (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = a.course_id) as total_expected FROM assignments a WHERE a.course_id IN (SELECT id FROM courses WHERE teacher_id = $teacher_id) LIMIT 5")->fetchAll();

// 3. Marks Distribution
$marks_distribution = $pdo->query("SELECT CASE WHEN score >= 90 THEN '90+' WHEN score >= 80 THEN '80-89' WHEN score >= 70 THEN '70-79' WHEN score >= 60 THEN '60-69' ELSE '<60' END as grade_range, COUNT(*) as count FROM quiz_attempts WHERE quiz_id IN (SELECT id FROM quizzes WHERE course_id IN (SELECT id FROM courses WHERE teacher_id = $teacher_id)) GROUP BY grade_range")->fetchAll();

// 4. Activity Totals
$total_assignments = $pdo->query("SELECT COUNT(*) FROM assignments WHERE course_id IN (SELECT id FROM courses WHERE teacher_id = $teacher_id)")->fetchColumn();
$total_quizzes = $pdo->query("SELECT COUNT(*) FROM quizzes WHERE course_id IN (SELECT id FROM courses WHERE teacher_id = $teacher_id)")->fetchColumn();
$total_materials = $pdo->query("SELECT COUNT(*) FROM materials WHERE course_id IN (SELECT id FROM courses WHERE teacher_id = $teacher_id)")->fetchColumn();
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Teacher Dashboard</h3>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3><?= $course_count ?></h3>
                        <p>My Courses</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3><?= $total_students ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3><?= $total_assignments ?></h3>
                        <p>Total Assignments</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box text-bg-info">
                    <div class="inner">
                        <h3><?= $total_quizzes ?></h3>
                        <p>Total Quizzes</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- NEW ANALYTICS SECTION -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Enrollment per Course</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="enrollmentChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title">Submission Rates (%)</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="submissionChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4 mb-4">
            <div class="col-md-12">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title">Marks Distribution</h3>
                    </div>
                    <div class="card-body text-center">
                        <canvas id="marksChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. Enrollment Chart
    const enrCtx = document.getElementById('enrollmentChart').getContext('2d');
    new Chart(enrCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($enrollment_stats, 'title')) ?>,
            datasets: [{
                label: 'Students Enrolled',
                data: <?= json_encode(array_column($enrollment_stats, 'student_count')) ?>,
                backgroundColor: '#007bff'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // 2. Submission Chart
    const subCtx = document.getElementById('submissionChart').getContext('2d');
    new Chart(subCtx, {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($submission_stats, 'title')) ?>,
            datasets: [{
                data: <?= json_encode(array_map(fn($s) => $s['total_expected'] > 0 ? round(($s['submissions'] / $s['total_expected']) * 100, 1) : 0, $submission_stats)) ?>,
                backgroundColor: ['#28a745', '#17a2b8', '#ffc107', '#dc3545', '#6610f2']
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.raw + '% Rate';
                        }
                    }
                }
            }
        }
    });

    // 3. Marks Chart
    const marksCtx = document.getElementById('marksChart').getContext('2d');
    new Chart(marksCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($marks_distribution, 'grade_range')) ?>,
            datasets: [{
                label: 'Student Count',
                data: <?= json_encode(array_column($marks_distribution, 'count')) ?>,
                backgroundColor: '#17a2b8'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>
