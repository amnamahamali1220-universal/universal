<?php
require_once '../../core/session.php';
require_once '../../core/teacher_roles.php';
checkRole(['course_instructor', 'teacher', 'admin']);
require_once '../../core/db.php';
require_once '../../includes/header.php';

$course_id = $_GET['course_id'] ?? 0;
$teacher_id = $_SESSION['user_id'];

// Verify Ownership
if ($_SESSION['role'] === 'super_admin' || $_SESSION['role'] === 'admin') {
    // Admins can upload to any course if they are explicitly assigned, or it bypasses.
    // Let's just allow them to view all courses for the selector
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->execute([$course_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$course_id, $teacher_id]);
}
$course = $stmt->fetch();

if (!$course) {
    // Instead of dying showing a misleading "Dashboard" error, show a friendlier error and standard layout
    echo '<div class="app-content-header"><div class="container-fluid"><div class="row"><div class="col-sm-6"><h3 class="mb-0">Course Content</h3></div></div></div></div>';
    echo '<div class="app-content"><div class="container-fluid"><div class="alert alert-danger">Please select a valid course from your dashboard first.</div><a href="instructor_dash.php" class="btn btn-primary">Go to Dashboard</a></div></div>';
    require_once '../../includes/footer.php';
    exit;
}

// Handle Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $type = $_POST['type'];
    
    $file_path = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $uploadDir = '../../uploads/materials/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        $fileName = time() . '_' . basename($_FILES['file']['name']);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $fileName)) {
            $file_path = 'uploads/materials/' . $fileName;
        }
    }

    $stmt = $pdo->prepare("INSERT INTO materials (course_id, title, type, file_path) VALUES (?, ?, ?, ?)");
    if ($stmt->execute([$course_id, $title, $type, $file_path])) {
        require_once '../../includes/activity_logger.php';
        logActivity($pdo, $_SESSION['user_id'], "Uploaded material: " . $title . " for course_id: " . $course_id);
        
        require_once '../../includes/notification_helper.php';
        notifyCourseStudents($pdo, $course_id, "New Lecture Posted: " . $title, "dashboards/student/course_view.php?id=".$course_id);
        
        $success = "Material uploaded successfully!";
    } else {
        $error = "Failed to upload material.";
    }
}

// Fetch existing materials
$stmt = $pdo->prepare("SELECT * FROM materials WHERE course_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$course_id]);
$materials = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Upload Lectures: <?= htmlspecialchars($course['title']) ?></h3>
            </div>
            <div class="col-sm-6 text-end">
                <a href="instructor_dash.php" class="btn btn-secondary">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-5">
                <div class="card card-outline card-info">
                    <div class="card-header"><h3 class="card-title">Add New Material</h3></div>
                    <div class="card-body">
                        <?php if(isset($success)): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>
                        <?php if(isset($error)): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>

                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="pdf">PDF Document</option>
                                    <option value="video">Video</option>
                                    <option value="doc">Document</option>
                                    <option value="link">Web Link</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">File</label>
                                <input type="file" name="file" class="form-control">
                            </div>
                            <button type="submit" class="btn btn-info d-block w-100">Upload</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card card-outline card-secondary">
                    <div class="card-header"><h3 class="card-title">Existing Materials</h3></div>
                    <div class="card-body">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Uploaded</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($materials as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['title']) ?></td>
                                    <td><span class="badge bg-secondary"><?= strtoupper($m['type']) ?></span></td>
                                    <td><?= date('M d, Y', strtotime($m['uploaded_at'])) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL . $m['file_path'] ?>" target="_blank" class="btn btn-xs btn-primary">View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if(empty($materials)): ?>
                                    <tr><td colspan="4" class="text-center">No materials found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
