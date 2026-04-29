<?php
require_once '../../core/session.php';
checkRole(['teacher', 'super_admin']);
require_once '../../core/db.php';

$sub_id = $_GET['submission_id'] ?? 0;

// Fetch Submission and Assignment details
$stmt = $pdo->prepare("SELECT s.*, a.title as assignment_title, a.id as assignment_id, u.name as student_name 
                       FROM submissions s 
                       JOIN assignments a ON s.assignment_id = a.id 
                       JOIN users u ON s.student_id = u.id 
                       WHERE s.id = ?");
$stmt->execute([$sub_id]);
$submission = $stmt->fetch();

if (!$submission) {
    die("Submission not found.");
}

// Fetch Rubric
$rStmt = $pdo->prepare("SELECT * FROM rubrics WHERE assignment_id = ?");
$rStmt->execute([$submission['assignment_id']]);
$rubric = $rStmt->fetch();

if (!$rubric) {
    die("No rubric found for this assignment.");
}

// Fetch Criteria and Levels
$cStmt = $pdo->prepare("SELECT * FROM rubric_criteria WHERE rubric_id = ?");
$cStmt->execute([$rubric['id']]);
$criteria = $cStmt->fetchAll();

foreach ($criteria as &$c) {
    $lStmt = $pdo->prepare("SELECT * FROM rubric_levels WHERE criteria_id = ? ORDER BY points DESC");
    $lStmt->execute([$c['id']]);
    $c['levels'] = $lStmt->fetchAll();
}

// Calculate Max Points Possible
$max_points = 0;
foreach ($criteria as $c) {
    $maxLevel = 0;
    foreach ($c['levels'] as $l) {
        if ($l['points'] > $maxLevel) $maxLevel = $l['points'];
    }
    $max_points += ($maxLevel * $c['weight']);
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rubric_grade'])) {
    $total_score = 0;
    $feedback_details = [];
    
    foreach ($criteria as $c) {
        $c_id = $c['id'];
        if (isset($_POST['criteria'][$c_id])) {
            $selected_level_id = $_POST['criteria'][$c_id];
            
            // Find points for this level
            $points = 0;
            $level_desc = "";
            foreach ($c['levels'] as $l) {
                if ($l['id'] == $selected_level_id) {
                    $points = $l['points'];
                    $level_desc = $l['description'];
                    break;
                }
            }
            $score = $points * $c['weight'];
            $total_score += $score;
            
            $feedback_details[] = "{$c['name']}: {$points} pts (Weight x{$c['weight']}) - {$level_desc}";
        }
    }
    
    // Convert to percentage or out of 100
    $final_grade = ($max_points > 0) ? round(($total_score / $max_points) * 100, 2) : 0;
    
    $teacher_feedback = $_POST['general_feedback'] ?? '';
    $full_feedback = "Rubric Breakdown:\n" . implode("\n", $feedback_details) . "\n\nGeneral Feedback: " . $teacher_feedback;
    
    $up = $pdo->prepare("UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?");
    $up->execute([$final_grade, $full_feedback, $sub_id]);
    
    header("Location: grade_assignment.php?id=" . $submission['assignment_id']);
    exit;
}

require_once '../../includes/header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-10">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header bg-dark text-white rounded-top-4 py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-clipboard-check me-2"></i> Rubric Grading: <?= htmlspecialchars($student_name ?? $submission['student_name']) ?></h4>
                <a href="grade_assignment.php?id=<?= $submission['assignment_id'] ?>" class="btn btn-sm btn-outline-light">Back</a>
            </div>
            <div class="card-body p-4">
                <h5 class="text-primary mb-3"><?= htmlspecialchars($rubric['title']) ?></h5>
                <p class="text-muted">Assignment: <?= htmlspecialchars($submission['assignment_title']) ?></p>
                <p><strong>Submission File:</strong> <a href="<?= BASE_URL . $submission['file_path'] ?>" target="_blank">View File</a></p>
                
                <hr>
                
                <form method="POST">
                    <?php foreach($criteria as $c): ?>
                    <div class="mb-4 p-3 bg-light rounded border">
                        <h6 class="fw-bold mb-3"><?= htmlspecialchars($c['name']) ?> <span class="badge bg-secondary ms-2">Weight: x<?= $c['weight'] ?></span></h6>
                        <div class="row row-cols-1 row-cols-md-3 g-3">
                            <?php foreach($c['levels'] as $l): ?>
                            <div class="col">
                                <label class="w-100 h-100">
                                    <div class="card h-100 border-primary rubric-level-card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="criteria[<?= $c['id'] ?>]" value="<?= $l['id'] ?>" required>
                                                <label class="form-check-label fw-bold">
                                                    <?= $l['points'] ?> Points
                                                </label>
                                            </div>
                                            <p class="small text-muted mt-2 mb-0"><?= htmlspecialchars($l['description']) ?></p>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="mb-4">
                        <label class="form-label fw-bold">General Feedback</label>
                        <textarea name="general_feedback" class="form-control" rows="3" placeholder="Additional comments..."></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Maximum possible score: <strong><?= $max_points ?></strong> points (converted to 100%)</span>
                        <button type="submit" name="submit_rubric_grade" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm"><i class="fas fa-check-circle me-2"></i> Save Rubric Grade</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.rubric-level-card:hover { background-color: #e9ecef; cursor: pointer; }
.form-check-input:checked + label { color: #0d6efd; }
.form-check-input:checked ~ p { font-weight: bold; }
input[type="radio"]:checked { background-color: #0d6efd; border-color: #0d6efd; }
</style>

<?php require_once '../../includes/footer.php'; ?>
