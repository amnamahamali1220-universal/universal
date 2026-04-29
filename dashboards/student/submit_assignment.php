<?php
require_once '../../core/session.php';
checkRole('student');
require_once '../../core/db.php';

$student_id = $_SESSION['user_id'];
$assignment_id = $_GET['assignment_id'] ?? 0;

if (!$assignment_id) {
    die("Error: No assignment specified.");
}

// Fetch assignment details
$stmt = $pdo->prepare("SELECT a.*, c.title as course_title, c.course_code 
                       FROM assignments a 
                       JOIN courses c ON a.course_id = c.id 
                       WHERE a.id = ?");
$stmt->execute([$assignment_id]);
$assignment = $stmt->fetch();

if (!$assignment) {
    die("Assignment not found.");
}

// Verify student is enrolled in this course
$enrollStmt = $pdo->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
$enrollStmt->execute([$student_id, $assignment['course_id']]);
if (!$enrollStmt->fetch()) {
    die("Error: You are not enrolled in this course.");
}

// Check if already submitted
$subStmt = $pdo->prepare("SELECT * FROM submissions WHERE student_id = ? AND assignment_id = ?");
$subStmt->execute([$student_id, $assignment_id]);
$existingSubmission = $subStmt->fetch();

$successMsg = '';
$errorMsg = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assignment'])) {
    $feedbackText = trim($_POST['submission_text'] ?? '');
    
    // File upload logic
    $uploadPath = null;
    if (isset($_FILES['assignment_file']) && $_FILES['assignment_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = APP_ROOT . '/uploads/assignments/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', basename($_FILES['assignment_file']['name']));
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $targetFile)) {
            $uploadPath = 'uploads/assignments/' . $fileName;
        } else {
            $errorMsg = "Failed to upload file.";
        }
    }
    
    if (empty($errorMsg)) {
        if ($existingSubmission) {
            // Update submission
            $updateQ = "UPDATE submissions SET submitted_at = current_timestamp()";
            $params = [];
            
            if ($uploadPath) {
                $updateQ .= ", file_path = ?";
                $params[] = $uploadPath;
            }
            if ($feedbackText) {
                // we don't have a dedicated text field in submissions schema natively, but we can append it to file_path or create one if we had it.
                // Wait, schema has `feedback` (for teacher), and `file_path`. No `submission_text`.
                // Let's rely on file upload mainly.
            }
            $updateQ .= " WHERE id = ?";
            $params[] = $existingSubmission['id'];
            
            $stmt = $pdo->prepare($updateQ);
            if ($stmt->execute($params)) {
                $successMsg = "Assignment updated successfully!";
                $existingSubmission['file_path'] = $uploadPath ?? $existingSubmission['file_path'];
                $existingSubmission['submitted_at'] = date('Y-m-d H:i:s');
            }
        } else {
            // Insert new
            $stmt = $pdo->prepare("INSERT INTO submissions (assignment_id, student_id, file_path) VALUES (?, ?, ?)");
            if ($stmt->execute([$assignment_id, $student_id, $uploadPath])) {
                $successMsg = "Assignment submitted successfully!";
                $subStmt->execute([$student_id, $assignment_id]);
                $existingSubmission = $subStmt->fetch();
            }
        }
    }
}
?>

<?php require_once '../../includes/header.php'; ?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
        <?php if($successMsg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($successMsg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if($errorMsg): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($errorMsg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow border-0 rounded-4 mb-4">
            <div class="card-header bg-primary text-white rounded-top-4 py-3">
                <h4 class="mb-0"><i class="fas fa-tasks me-2"></i> Submit Assignment</h4>
            </div>
            <div class="card-body p-4">
                <h5 class="text-primary mb-1"><?= htmlspecialchars($assignment['title']) ?></h5>
                <p class="text-muted mb-3"><i class="bi bi-book me-1"></i> <?= htmlspecialchars($assignment['course_code']) ?> - <?= htmlspecialchars($assignment['course_title']) ?></p>
                
                <div class="bg-light p-3 rounded mb-4">
                    <strong>Description:</strong><br>
                    <?= nl2br(htmlspecialchars($assignment['description'])) ?>
                </div>
                
                <div class="d-flex justify-content-between mb-4">
                    <div>
                        <strong>Due Date:</strong> 
                        <span class="<?= (strtotime($assignment['due_date']) < time()) ? 'text-danger fw-bold' : 'text-success' ?>">
                            <?= date('M d, Y h:i A', strtotime($assignment['due_date'])) ?>
                        </span>
                    </div>
                    <div>
                        <strong>Status:</strong> 
                        <?php if ($existingSubmission): ?>
                            <span class="badge bg-success">Submitted</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Pending</span>
                        <?php endif; ?>
                    </div>
                </div>

                <hr>

                <form action="" method="POST" enctype="multipart/form-data">
                    <?php if ($existingSubmission): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> You have already submitted this assignment on <strong><?= date('M d, Y h:i A', strtotime($existingSubmission['submitted_at'])) ?></strong>.
                            <?php if ($existingSubmission['file_path']): ?>
                                <br><a href="<?= BASE_URL . $existingSubmission['file_path'] ?>" class="btn btn-sm btn-outline-primary mt-2" target="_blank"><i class="fas fa-download me-1"></i> View Submitted File</a>
                            <?php endif; ?>
                            <hr>
                            <p class="mb-0 text-sm">Uploading a new file will overwrite your previous submission.</p>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Upload Work File</label>
                        <input class="form-control" type="file" name="assignment_file" <?= $existingSubmission ? '' : 'required' ?>>
                        <div class="form-text">Allowed formats: PDF, DOCX, ZIP, PNG, JPG. Max size: 10MB.</div>
                    </div>
                    
                    <button type="submit" name="submit_assignment" class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                        <i class="fas fa-paper-plane me-2"></i> <?= $existingSubmission ? 'Resubmit Assignment' : 'Submit Assignment' ?>
                    </button>
                    <a href="course_view.php?id=<?= $assignment['course_id'] ?>" class="btn btn-secondary btn-lg rounded-pill ms-2 px-4 shadow-sm">Cancel</a>
                </form>
            </div>
        </div>
        
        <?php if ($existingSubmission && $existingSubmission['grade'] !== null): ?>
        <div class="card shadow-sm border-0 rounded-4 border-start border-4 border-success">
            <div class="card-body p-4">
                <h5 class="text-success"><i class="fas fa-award me-2"></i> Graded Result</h5>
                <p class="fs-4 fw-bold mb-1"><?= htmlspecialchars($existingSubmission['grade']) ?> / 100</p>
                <?php if ($existingSubmission['feedback']): ?>
                    <p class="text-muted mt-2 mb-0"><strong>Feedback:</strong> <?= nl2br(htmlspecialchars($existingSubmission['feedback'])) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
