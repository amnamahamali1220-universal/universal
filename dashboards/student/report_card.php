<?php
require_once '../../core/session.php';
checkRole(['student', 'teacher', 'admin']); // Allow others to view if they pass ID
require_once '../../core/db.php';

$student_id = $_SESSION['user_id'];

// If a non-student is viewing this, they must provide a student_id
if ($_SESSION['role'] !== 'student') {
    if (isset($_GET['student_id'])) {
        $student_id = $_GET['student_id'];
    } else {
        die("Error: Please provide a student ID in the URL to view their report card (e.g., ?student_id=3).");
    }
}

// Fetch Student Profile
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND role = 'student'");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

if (!$student) {
    die("Student record not found.");
}

// Fetch Courses and Grades
$sql = "SELECT c.id as course_id, c.course_code, c.title as course_title,
            (SELECT AVG(grade) FROM submissions s JOIN assignments a ON s.assignment_id = a.id WHERE a.course_id = c.id AND s.student_id = ?) as avg_assignment,
            (SELECT AVG(score) FROM quiz_attempts qa JOIN quizzes q ON qa.quiz_id = q.id WHERE q.course_id = c.id AND qa.student_id = ?) as avg_quiz,
            (SELECT COUNT(*) FROM attendance WHERE course_id = c.id AND student_id = ? AND status IN ('present', 'late')) as present_count,
            (SELECT COUNT(*) FROM attendance WHERE course_id = c.id AND student_id = ?) as total_classes
        FROM enrollments e
        JOIN courses c ON e.course_id = c.id
        WHERE e.student_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$student_id, $student_id, $student_id, $student_id, $student_id]);
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Card - <?= htmlspecialchars($student['name']) ?></title>
    <!-- Use AdminLTE/Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    <!-- html2pdf -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; padding: 20px; }
        .report-card { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); max-width: 900px; margin: 0 auto; }
        .school-header { text-align: center; border-bottom: 2px solid #2i4170; padding-bottom: 20px; margin-bottom: 30px; }
        .school-header h1 { margin: 0; color: #1a365d; font-weight: bold; }
        .student-info { display: flex; justify-content: space-between; margin-bottom: 30px; background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .student-info p { margin: 5px 0; font-size: 1.1rem; }
        .table th { background-color: #f8f9fa; color: #495057; border-bottom: 2px solid #dee2e6; font-size: 0.9rem; }
        .table td { vertical-align: middle; }
        .gpa-box { float: right; background: #e9ecef; padding: 15px 30px; border-radius: 5px; margin-top: 20px; border: 1px solid #ced4da; }
        .gpa-box h3 { margin: 0; color: #212529; }
        .signature-area { margin-top: 80px; display: flex; justify-content: space-between; }
        .signature-line { width: 200px; border-top: 1px solid #000; text-align: center; padding-top: 5px; }
        .no-print { margin-bottom: 20px; text-align: center; }
        .mark-breakdown { font-size: 0.8rem; color: #6c757d; }
    </style>
</head>
<body>

<div class="no-print">
    <a href="index.php" class="btn btn-secondary me-2"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    <button onclick="generatePDF()" class="btn btn-primary"><i class="bi bi-file-earmark-pdf"></i> Download PDF Report</button>
</div>

<div class="report-card" id="report_card_content">
    <div class="school-header">
        <h1>Universal Educational Institute</h1>
        <p class="text-muted mb-0">Official Student Academic Report</p>
        <small>Date generated: <?= date('M d, Y') ?></small>
    </div>

    <div class="student-info">
        <div>
            <p><strong>Student Name:</strong> <?= htmlspecialchars($student['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($student['email']) ?></p>
        </div>
        <div class="text-end">
            <p><strong>Registration No:</strong> <?= htmlspecialchars($student['registration_no'] ?: 'N/A') ?></p>
            <p><strong>Identity No:</strong> <?= htmlspecialchars($student['identity_no'] ?: 'N/A') ?></p>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr class="text-center">
                <th>Course Code</th>
                <th>Subject Name</th>
                <th>Assig. (10)</th>
                <th>Atten. (5)</th>
                <th>Quiz (5)</th>
                <th>Total (20)</th>
                <th>Grade</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($courses as $c): 
                // Assignment Mark (scaled to 10)
                $avg_ass = $c['avg_assignment'] !== null ? $c['avg_assignment'] : 0;
                $mark_ass = ($avg_ass / 100) * 10;
                
                // Quiz Mark (scaled to 5)
                $avg_qui = $c['avg_quiz'] !== null ? $c['avg_quiz'] : 0;
                $mark_qui = ($avg_qui / 100) * 5;
                
                // Attendance Mark (scaled to 5)
                $pres = $c['present_count'];
                $total_att = $c['total_classes'];
                $mark_att = ($total_att > 0) ? ($pres / $total_att) * 5 : 0;
                
                $total_mark = $mark_ass + $mark_att + $mark_qui;
                $final_percentage = ($total_mark / 20) * 100;

                $scale = getGradeScale($final_percentage);
                $total_points += $scale['point'];
                $valid_courses++;
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($c['course_code']) ?></strong></td>
                <td><?= htmlspecialchars($c['course_title']) ?></td>
                <td class="text-center"><?= number_format($mark_ass, 1) ?></td>
                <td class="text-center"><?= number_format($mark_att, 1) ?></td>
                <td class="text-center"><?= number_format($mark_qui, 1) ?></td>
                <td class="text-center fw-bold"><?= number_format($total_mark, 1) ?></td>
                <td class="text-center fw-bold text-primary"><?= $scale['grade'] ?></td>
            </tr>
            <?php endforeach; ?>

            <?php if(empty($courses)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted">Student is not enrolled in any courses.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="gpa-box shadow-sm">
        <?php 
            $final_gpa = ($valid_courses > 0) ? ($total_points / $valid_courses) : 0;
        ?>
        <h3>Term GPA: <span class="text-success"><?= number_format($final_gpa, 2) ?></span></h3>
    </div>
    <div class="clearfix"></div>

    <div class="signature-area">
        <div class="signature-line">
            Class Advisor
        </div>
        <div class="signature-line">
            Principal / Dean
        </div>
    </div>
</div>

<script>
function generatePDF() {
    const element = document.getElementById('report_card_content');
    const opt = {
        margin:       0.5,
        filename:     'Report_Card_<?= preg_replace('/[^a-zA-Z0-9_]/', '_', $student['name']) ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
    };
    
    // Create PDF
    html2pdf().set(opt).from(element).save();
}
</script>

</body>
</html>
