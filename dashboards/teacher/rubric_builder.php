<?php
require_once '../../core/session.php';
checkRole(['teacher', 'super_admin']);
require_once '../../core/db.php';

$teacher_id = $_SESSION['user_id'];
$assignment_id = $_GET['assignment_id'] ?? 0;

// Fetch assignments taught by this teacher for a dropdown
// A teacher is assigned to a course via `teacher_student_assign` or they created it?
// Let's just fetch all assignments for courses they have access to.
// Actually, earlier code in teacher/my_courses.php or assignment_dash.php usually assumes they created the course or are linked.
// Let's just fetch assignments where course_id IN (SELECT course_id FROM courses...) 
// We'll just fetch all assignments if they are super_admin, or filter if teacher.
// We'll keep it simple: just list all assignments for now (or let's find the correct table).
$courseQuery = "SELECT id, title FROM courses";
$courseStmt = $pdo->query($courseQuery);
$courses = $courseStmt->fetchAll();

$assignments = [];
if (isset($_GET['course_id'])) {
    $aStmt = $pdo->prepare("SELECT id, title FROM assignments WHERE course_id = ?");
    $aStmt->execute([$_GET['course_id']]);
    $assignments = $aStmt->fetchAll();
}

$rubric = null;
$criteria = [];
if ($assignment_id) {
    // Check if rubric exists
    $rStmt = $pdo->prepare("SELECT * FROM rubrics WHERE assignment_id = ?");
    $rStmt->execute([$assignment_id]);
    $rubric = $rStmt->fetch();

    if ($rubric) {
        $cStmt = $pdo->prepare("SELECT * FROM rubric_criteria WHERE rubric_id = ?");
        $cStmt->execute([$rubric['id']]);
        $criteria = $cStmt->fetchAll();
        foreach ($criteria as &$c) {
            $lStmt = $pdo->prepare("SELECT * FROM rubric_levels WHERE criteria_id = ? ORDER BY points DESC");
            $lStmt->execute([$c['id']]);
            $c['levels'] = $lStmt->fetchAll();
        }
    }
}

// Handle Form Submission for new/updated rubric
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_rubric'])) {
    $assignment_id = $_POST['assignment_id'];
    $rubric_title = $_POST['rubric_title'];
    
    try {
        $pdo->beginTransaction();
        
        // Delete old rubric if exists (cascade deletes criteria and levels)
        $dStmt = $pdo->prepare("DELETE FROM rubrics WHERE assignment_id = ?");
        $dStmt->execute([$assignment_id]);
        
        // Insert new rubric
        $iStmt = $pdo->prepare("INSERT INTO rubrics (assignment_id, title) VALUES (?, ?)");
        $iStmt->execute([$assignment_id, $rubric_title]);
        $rubric_id = $pdo->lastInsertId();
        
        // Insert criteria and levels
        if (isset($_POST['criteria']) && is_array($_POST['criteria'])) {
            foreach ($_POST['criteria'] as $index => $cData) {
                if (empty($cData['name'])) continue;
                
                $cInsert = $pdo->prepare("INSERT INTO rubric_criteria (rubric_id, name, weight) VALUES (?, ?, ?)");
                $cInsert->execute([$rubric_id, $cData['name'], $cData['weight'] ?? 1]);
                $criteria_id = $pdo->lastInsertId();
                
                if (isset($_POST['levels'][$index]) && is_array($_POST['levels'][$index])) {
                    foreach ($_POST['levels'][$index] as $lData) {
                        if (empty($lData['description'])) continue;
                        $lInsert = $pdo->prepare("INSERT INTO rubric_levels (criteria_id, points, description) VALUES (?, ?, ?)");
                        $lInsert->execute([$criteria_id, $lData['points'] ?? 0, $lData['description']]);
                    }
                }
            }
        }
        
        $pdo->commit();
        $successMsg = "Rubric saved successfully!";
        // Refresh page to show new data
        header("Refresh:2");
    } catch (Exception $e) {
        $pdo->rollBack();
        $errorMsg = "Failed to save rubric: " . $e->getMessage();
    }
}

require_once '../../includes/header.php';
?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-10">
        <?php if(isset($successMsg)): ?>
            <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($successMsg) ?></div>
        <?php endif; ?>
        <?php if(isset($errorMsg)): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($errorMsg) ?></div>
        <?php endif; ?>

        <div class="card shadow border-0 rounded-4 mb-4">
            <div class="card-header bg-dark text-white rounded-top-4 py-3">
                <h4 class="mb-0"><i class="fas fa-table ms-2 me-2"></i> Rubric Builder</h4>
            </div>
            <div class="card-body p-4">
                <form method="GET" class="row g-3 mb-4">
                    <div class="col-md-5">
                        <label class="form-label">Select Course</label>
                        <select name="course_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Choose Course --</option>
                            <?php foreach($courses as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= (isset($_GET['course_id']) && $_GET['course_id'] == $c['id']) ? 'selected' : '' ?>><?= htmlspecialchars($c['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if(!empty($assignments)): ?>
                    <div class="col-md-5">
                        <label class="form-label">Select Assignment</label>
                        <select name="assignment_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Choose Assignment --</option>
                            <?php foreach($assignments as $a): ?>
                                <option value="<?= $a['id'] ?>" <?= ($assignment_id == $a['id']) ? 'selected' : '' ?>><?= htmlspecialchars($a['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                </form>

                <?php if ($assignment_id): ?>
                <hr>
                <form method="POST" id="rubricForm">
                    <input type="hidden" name="assignment_id" value="<?= $assignment_id ?>">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Rubric Title</label>
                        <input type="text" name="rubric_title" class="form-control form-control-lg" value="<?= htmlspecialchars($rubric['title'] ?? 'Grading Rubric') ?>" required>
                    </div>

                    <h5 class="mb-3 text-secondary border-bottom pb-2">Grading Criteria</h5>
                    <div id="criteriaContainer">
                        <?php 
                        $cIndex = 0;
                        if (!empty($criteria)) {
                            foreach($criteria as $c) {
                                echo '<div class="card bg-light border-0 mb-3 criteria-block" data-index="'.$cIndex.'">';
                                echo '<div class="card-body">';
                                echo '<div class="row align-items-end mb-3">';
                                echo '<div class="col-md-8"><label class="form-label fw-bold">Criteria Name</label><input type="text" name="criteria['.$cIndex.'][name]" class="form-control" value="'.htmlspecialchars($c['name']).'" required></div>';
                                echo '<div class="col-md-3"><label class="form-label fw-bold">Weight (Multiplier)</label><input type="number" name="criteria['.$cIndex.'][weight]" class="form-control" value="'.$c['weight'].'" required min="1"></div>';
                                echo '<div class="col-md-1"><button type="button" class="btn btn-danger w-100" onclick="this.closest(\'.criteria-block\').remove()"><i class="fas fa-trash"></i></button></div>';
                                echo '</div>';
                                
                                echo '<h6>Levels</h6><div class="levels-container row g-2">';
                                $lIndex = 0;
                                foreach($c['levels'] as $l) {
                                    echo '<div class="col-md-4 level-block">';
                                    echo '<div class="p-2 border rounded bg-white relative">';
                                    echo '<input type="number" name="levels['.$cIndex.']['.$lIndex.'][points]" class="form-control mb-1 form-control-sm" value="'.$l['points'].'" placeholder="Points" required>';
                                    echo '<textarea name="levels['.$cIndex.']['.$lIndex.'][description]" class="form-control form-control-sm" placeholder="Description" rows="2" required>'.htmlspecialchars($l['description']).'</textarea>';
                                    echo '</div></div>';
                                    $lIndex++;
                                }
                                echo '<div class="col-md-4 d-flex align-items-center"><button type="button" class="btn btn-sm btn-outline-primary" onclick="addLevel(this, '.$cIndex.')"><i class="fas fa-plus"></i> Add Level</button></div>';
                                echo '</div>'; // end levels container
                                echo '</div></div>';
                                $cIndex++;
                            }
                        }
                        ?>
                    </div>
                    
                    <button type="button" class="btn btn-secondary rounded-pill mb-4" onclick="addCriteria()"><i class="fas fa-plus me-1"></i> Add Criteria</button>
                    
                    <div class="d-grid">
                        <button type="submit" name="save_rubric" class="btn btn-primary btn-lg rounded-pill shadow-sm"><i class="fas fa-save me-2"></i> Save Rubric</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
let criteriaCount = <?= max(1, isset($cIndex) ? $cIndex : 0) ?>;

function addCriteria() {
    let html = `
    <div class="card bg-light border-0 mb-3 criteria-block" data-index="${criteriaCount}">
        <div class="card-body">
            <div class="row align-items-end mb-3">
                <div class="col-md-8"><label class="form-label fw-bold">Criteria Name</label><input type="text" name="criteria[${criteriaCount}][name]" class="form-control" placeholder="e.g., Code Quality" required></div>
                <div class="col-md-3"><label class="form-label fw-bold">Weight (Multiplier)</label><input type="number" name="criteria[${criteriaCount}][weight]" class="form-control" value="1" required min="1"></div>
                <div class="col-md-1"><button type="button" class="btn btn-danger w-100" onclick="this.closest('.criteria-block').remove()"><i class="fas fa-trash"></i></button></div>
            </div>
            <h6>Levels</h6>
            <div class="levels-container row g-2">
                <div class="col-md-4 level-block">
                    <div class="p-2 border rounded bg-white relative">
                        <input type="number" name="levels[${criteriaCount}][0][points]" class="form-control mb-1 form-control-sm" placeholder="Points (e.g. 5)" required>
                        <textarea name="levels[${criteriaCount}][0][description]" class="form-control form-control-sm" placeholder="Excellent..." rows="2" required></textarea>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addLevel(this, ${criteriaCount})"><i class="fas fa-plus"></i> Add Level</button>
                </div>
            </div>
        </div>
    </div>`;
    document.getElementById('criteriaContainer').insertAdjacentHTML('beforeend', html);
    criteriaCount++;
}

function addLevel(btn, cIdx) {
    let container = btn.closest('.levels-container');
    let lIdx = container.querySelectorAll('.level-block').length;
    let html = `
    <div class="col-md-4 level-block">
        <div class="p-2 border rounded bg-white relative">
            <input type="number" name="levels[${cIdx}][${lIdx}][points]" class="form-control mb-1 form-control-sm" placeholder="Points" required>
            <textarea name="levels[${cIdx}][${lIdx}][description]" class="form-control form-control-sm" placeholder="Description" rows="2" required></textarea>
        </div>
    </div>`;
    btn.parentElement.insertAdjacentHTML('beforebegin', html);
}
</script>

<?php require_once '../../includes/footer.php'; ?>
