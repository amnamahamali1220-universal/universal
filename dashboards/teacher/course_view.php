<?php
require_once '../../core/session.php';
checkRole('teacher');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$teacher_id = $_SESSION['user_id'];
$course_id = $_GET['id'] ?? 0;

// Verify Course Ownership
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$course_id, $teacher_id]);
$course = $stmt->fetch();

if (!$course) {
    echo "<div class='p-4'><h3>Course not found or access denied.</h3></div>";
    require_once '../../includes/footer.php';
    exit;
}

// Handle Form Submissions
// 1. Add Material
if (isset($_POST['add_material'])) {
    $title = $_POST['material_title'];
    $type = $_POST['material_type'];
    $file_path = '';

    // Handle File Upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $target_dir = "../../uploads/materials/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . '_' . basename($_FILES["file"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
            $file_path = "uploads/materials/" . $file_name;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO materials (course_id, title, type, file_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$course_id, $title, $type, $file_path]);
    
    require_once '../../includes/activity_logger.php';
    logActivity($pdo, $_SESSION['user_id'], "Uploaded material: " . $title . " for course_id: " . $course_id);
    
    require_once '../../includes/notification_helper.php';
    notifyCourseStudents($pdo, $course_id, "New Lecture Posted: " . $title, "dashboards/student/course_view.php?id=".$course_id);
    
    echo "<script>alert('Material Added');</script>";
}

// 2. Add Assignment
if (isset($_POST['add_assignment'])) {
    $title = $_POST['assignment_title'];
    $desc = $_POST['description'];
    $due = $_POST['due_date'];
    
    $stmt = $pdo->prepare("INSERT INTO assignments (course_id, title, description, due_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$course_id, $title, $desc, $due]);
    
    require_once '../../includes/activity_logger.php';
    logActivity($pdo, $_SESSION['user_id'], "Created assignment: " . $title . " for course_id: " . $course_id);
    
    require_once '../../includes/notification_helper.php';
    notifyCourseStudents($pdo, $course_id, "New Assignment Posted: " . $title, "dashboards/student/course_view.php?id=".$course_id);
    
    echo "<script>alert('Assignment Created');</script>";
}

// Fetch Data
$materials = $pdo->prepare("SELECT * FROM materials WHERE course_id = ? ORDER BY uploaded_at DESC");
$materials->execute([$course_id]);
$materials_list = $materials->fetchAll();

$assignments = $pdo->prepare("SELECT * FROM assignments WHERE course_id = ? ORDER BY created_at DESC");
$assignments->execute([$course_id]);
$assignments_list = $assignments->fetchAll();

$enrolled = $pdo->prepare("
    SELECT u.name, u.email, u.identity_no 
    FROM enrollments e 
    JOIN users u ON e.student_id = u.id 
    WHERE e.course_id = ?");
$enrolled->execute([$course_id]);
$students_list = $enrolled->fetchAll();

?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3><?= htmlspecialchars($course['title']) ?> <small class="text-muted">(<?= htmlspecialchars($course['course_code']) ?>)</small></h3>
            </div>
            <div class="col-sm-6 text-end">
                <a href="my_courses.php" class="btn btn-secondary">Back to Courses</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        
        <ul class="nav nav-tabs" id="courseTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="materials-tab" data-bs-toggle="tab" href="#materials" role="tab">Materials</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="assignments-tab" data-bs-toggle="tab" href="#assignments" role="tab">Assignments</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="students-tab" data-bs-toggle="tab" href="#students" role="tab">Students</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="quizzes-tab" data-bs-toggle="tab" href="#quizzes" role="tab">Quizzes</a>
            </li>
        </ul>

        <div class="tab-content p-3 border border-top-0 bg-white">
            
            <!-- Materials Tab -->
            <div class="tab-pane fade show active" id="materials" role="tabpanel">
                <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addMaterialModal">
                    <i class="bi bi-upload"></i> Upload Material
                </button>
                <div class="list-group">
                    <?php foreach($materials_list as $m): ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-file-earmark-text me-2"></i>
                                <strong><?= htmlspecialchars($m['title']) ?></strong>
                                <span class="badge bg-secondary ms-2"><?= $m['type'] ?></span>
                            </div>
                            <?php if($m['file_path']): ?>
                                <a href="<?= BASE_URL . $m['file_path'] ?>" class="btn btn-sm btn-outline-primary" target="_blank">Download</a>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($materials_list)) echo "<p class='text-muted'>No materials uploaded yet.</p>"; ?>
                </div>
            </div>

            <!-- Assignments Tab -->
            <div class="tab-pane fade" id="assignments" role="tabpanel">
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addAssignmentModal">
                    <i class="bi bi-plus-lg"></i> Create Assignment
                </button>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead><tr><th>Title</th><th>Due Date</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach($assignments_list as $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['title']) ?></td>
                                    <td><?= $a['due_date'] ?></td>
                                    <td>
                                        <a href="grade_assignment.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-info">View Submissions</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Students Tab -->
            <div class="tab-pane fade" id="students" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead><tr><th>Name</th><th>Email</th><th>ID No</th></tr></thead>
                        <tbody>
                            <?php foreach($students_list as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['name']) ?></td>
                                    <td><?= htmlspecialchars($s['email']) ?></td>
                                    <td><?= htmlspecialchars($s['identity_no']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Quizzes Tab -->
            <div class="tab-pane fade" id="quizzes" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4>Quizzes</h4>
                    <a href="quizzes.php?course_id=<?= $course_id ?>" class="btn btn-primary">
                        <i class="bi bi-gear"></i> Manage Quizzes
                    </a>
                </div>
                <div class="list-group">
                    <?php 
                    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE course_id = ?");
                    $stmt->execute([$course_id]);
                    $course_quizzes = $stmt->fetchAll();
                    foreach($course_quizzes as $cq): 
                    ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <i class="bi bi-question-circle me-2"></i>
                                <strong><?= htmlspecialchars($cq['title']) ?></strong>
                            </div>
                            <a href="quiz_results.php?id=<?= $cq['id'] ?>&course_id=<?= $course_id ?>" class="btn btn-sm btn-outline-success">View Results</a>
                        </div>
                    <?php endforeach; ?>
                    <?php if(empty($course_quizzes)) echo "<p class='text-muted'>No quizzes created for this course.</p>"; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Add Material Modal -->
<div class="modal fade" id="addMaterialModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Upload Material</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Title</label><input type="text" name="material_title" class="form-control" required></div>
                <div class="mb-3"><label>Type</label>
                    <select name="material_type" class="form-control">
                        <option value="pdf">PDF</option>
                        <option value="video">Video</option>
                        <option value="doc">Document</option>
                    </select>
                </div>
                <div class="mb-3"><label>File</label><input type="file" name="file" class="form-control" required></div>
                <input type="hidden" name="add_material" value="1">
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-success">Upload</button></div>
        </form>
    </div>
</div>

<!-- Add Assignment Modal -->
<div class="modal fade" id="addAssignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Create Assignment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Title</label><input type="text" name="assignment_title" class="form-control" required></div>
                <div class="mb-3"><label>Description</label><textarea name="description" class="form-control"></textarea></div>
                <div class="mb-3"><label>Due Date</label><input type="datetime-local" name="due_date" class="form-control" required></div>
                <input type="hidden" name="add_assignment" value="1">
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Create</button></div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
