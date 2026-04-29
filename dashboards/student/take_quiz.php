<?php
require_once '../../core/session.php';
checkRole('student');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$student_id = $_SESSION['user_id'];
$quiz_id = $_GET['id'] ?? 0;

// Fetch Quiz Info
$quiz_stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$quiz_stmt->execute([$quiz_id]);
$quiz = $quiz_stmt->fetch();

if (!$quiz) {
    die("Quiz not found.");
}

// Handle Submission
if (isset($_POST['submit_quiz'])) {
    $answers = $_POST['answers'] ?? []; // Array of question_id => selected_option
    $score = 0;
    $total = 0;
    
    foreach ($answers as $qid => $selected) {
        $stmt = $pdo->prepare("SELECT correct_option FROM questions WHERE id = ?");
        $stmt->execute([$qid]);
        $correct = $stmt->fetchColumn();
        if ($correct === $selected) {
            $score++;
        }
        $total++;
    }
    
    // Calculate percentage
    $final_score = ($total > 0) ? ($score / $total) * 100 : 0;
    
    // Save Attempt
    $stmt = $pdo->prepare("INSERT INTO quiz_attempts (quiz_id, student_id, score) VALUES (?, ?, ?)");
    $stmt->execute([$quiz_id, $student_id, $final_score]);
    
    echo "<div class='container p-5 text-center shadow mt-5 bg-white rounded'>";
    echo "<i class='bi bi-check-circle-fill text-success display-1'></i>";
    echo "<h2 class='mt-4'>Quiz Submitted!</h2>";
    echo "<p class='lead'>Your Score: <strong>".number_format($final_score, 1)."%</strong></p>";
    echo "<a href='quizzes.php' class='btn btn-primary btn-lg mt-3'>Back to My Quizzes</a>";
    echo "</div>";
    require_once '../../includes/footer.php';
    exit;
}

// Check if already attempted
$check = $pdo->prepare("SELECT score FROM quiz_attempts WHERE quiz_id = ? AND student_id = ?");
$check->execute([$quiz_id, $student_id]);
if ($check->rowCount() > 0) {
    echo "<div class='container p-5 text-center shadow mt-5 bg-white rounded'>";
    echo "<i class='bi bi-info-circle-fill text-info display-1'></i>";
    echo "<h2 class='mt-4'>Already Attempted</h2>";
    echo "<p class='lead'>You have already taken this quiz. Score: <strong>".number_format($check->fetchColumn(), 1)."%</strong></p>";
    echo "<a href='quizzes.php' class='btn btn-secondary mt-3'>Back</a>";
    echo "</div>";
    require_once '../../includes/footer.php';
    exit;
}

// Fetch Questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3>Take Quiz: <?= htmlspecialchars($quiz['title']) ?></h3>
            </div>
            <div class="col-sm-6 text-end">
                <a href="quizzes.php" class="btn btn-secondary">Cancel</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form method="post">
                    <?php foreach($questions as $index => $q): ?>
                    <div class="card mb-4 shadow-sm">
                        <div class="card-header bg-light">
                            <strong>Question <?= $index+1 ?></strong>
                        </div>
                        <div class="card-body">
                            <h5 class="mb-4"><?= htmlspecialchars($q['question_text']) ?></h5>
                            
                            <div class="list-group">
                                <label class="list-group-item list-group-item-action py-3">
                                    <input class="form-check-input me-2" type="radio" name="answers[<?= $q['id'] ?>]" value="A" required>
                                    <strong>A)</strong> <?= htmlspecialchars($q['option_a']) ?>
                                </label>
                                <label class="list-group-item list-group-item-action py-3">
                                    <input class="form-check-input me-2" type="radio" name="answers[<?= $q['id'] ?>]" value="B">
                                    <strong>B)</strong> <?= htmlspecialchars($q['option_b']) ?>
                                </label>
                                <label class="list-group-item list-group-item-action py-3">
                                    <input class="form-check-input me-2" type="radio" name="answers[<?= $q['id'] ?>]" value="C">
                                    <strong>C)</strong> <?= htmlspecialchars($q['option_c']) ?>
                                </label>
                                <label class="list-group-item list-group-item-action py-3">
                                    <input class="form-check-input me-2" type="radio" name="answers[<?= $q['id'] ?>]" value="D">
                                    <strong>D)</strong> <?= htmlspecialchars($q['option_d']) ?>
                                </label>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    
                    <div class="text-center mb-5">
                        <button type="submit" name="submit_quiz" class="btn btn-primary btn-xl px-5 py-3 shadow" onclick="return confirm('Are you sure you want to submit?')">
                            <i class="bi bi-send-fill me-2"></i> Submit Quiz
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
