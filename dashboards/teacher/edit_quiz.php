<?php
require_once '../../core/session.php';
checkRole('teacher');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$quiz_id = $_GET['id'] ?? 0;

// Add Question
if (isset($_POST['add_question'])) {
    $text = $_POST['question_text'];
    $oa = $_POST['option_a'];
    $ob = $_POST['option_b'];
    $oc = $_POST['option_c'];
    $od = $_POST['option_d'];
    $correct = $_POST['correct'];
    
    $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$quiz_id, $text, $oa, $ob, $oc, $od, $correct]);
}

// Fetch Questions
$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Edit Quiz Questions</h3></div>
            <div class="col-sm-6 text-end">
                <a href="quizzes.php?course_id=<?= $_GET['course_id'] ?? 0 // Note: might need to fetch course_id from quiz if not passed ?>" class="btn btn-secondary">Done</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Add Question</div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-2"><textarea name="question_text" class="form-control" placeholder="Question" required></textarea></div>
                            <div class="mb-2"><input type="text" name="option_a" class="form-control" placeholder="Option A" required></div>
                            <div class="mb-2"><input type="text" name="option_b" class="form-control" placeholder="Option B" required></div>
                            <div class="mb-2"><input type="text" name="option_c" class="form-control" placeholder="Option C" required></div>
                            <div class="mb-2"><input type="text" name="option_d" class="form-control" placeholder="Option D" required></div>
                            <div class="mb-2">
                                <label>Correct Option</label>
                                <select name="correct" class="form-control">
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="C">C</option>
                                    <option value="D">D</option>
                                </select>
                            </div>
                            <button type="submit" name="add_question" class="btn btn-success w-100">Add</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <?php foreach($questions as $index => $q): ?>
                <div class="card mb-2">
                    <div class="card-body">
                        <strong>Q<?= $index+1 ?>: <?= htmlspecialchars($q['question_text']) ?></strong>
                        <ul class="list-unstyled mt-2">
                            <li class="<?= $q['correct_option']=='A'?'text-success fw-bold':'' ?>">A: <?= htmlspecialchars($q['option_a']) ?></li>
                            <li class="<?= $q['correct_option']=='B'?'text-success fw-bold':'' ?>">B: <?= htmlspecialchars($q['option_b']) ?></li>
                            <li class="<?= $q['correct_option']=='C'?'text-success fw-bold':'' ?>">C: <?= htmlspecialchars($q['option_c']) ?></li>
                            <li class="<?= $q['correct_option']=='D'?'text-success fw-bold':'' ?>">D: <?= htmlspecialchars($q['option_d']) ?></li>
                        </ul>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
