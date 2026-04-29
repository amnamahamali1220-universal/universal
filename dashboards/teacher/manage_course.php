<?php
require_once '../../core/session.php';
checkRole('teacher');
require_once '../../core/db.php';
require_once '../../includes/header.php';



$courseId = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->execute([$courseId, $_SESSION['user_id']]);
$course = $stmt->fetch();

if (!$course) {
    die("Course not found or access denied.");
}

// Handle Material Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_material'])) {
    $title = $_POST['title'];
    $type = $_POST['type'];
    $link = $_POST['link'] ?? ''; // For video/text
    
    // Simple file handling for PDF (In real app, validation needed)
    $filePath = $link;
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $uploadDir = '../../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir);
        $fileName = time() . '_' . basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $fileName);
        $filePath = 'uploads/' . $fileName;
    }

    $stmt = $pdo->prepare("INSERT INTO course_materials (course_id, type, title, file_path) VALUES (?, ?, ?, ?)");
    $stmt->execute([$courseId, $type, $title, $filePath]);
}

// Fetch Materials
$materials = $pdo->prepare("SELECT * FROM course_materials WHERE course_id = ? ORDER BY created_at DESC");
$materials->execute([$courseId]);
$materials = $materials->fetchAll();

// Fetch Enrolled Students
$students = $pdo->prepare("
    SELECT u.name, u.email, e.enrolled_at 
    FROM student_enrollments e 
    JOIN users u ON e.student_id = u.id 
    WHERE e.course_id = ?
");
$students->execute([$courseId]);
$students = $students->fetchAll();
?>

<div class="row">
    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-body box-profile">
                <h3 class="profile-username text-center"><?= htmlspecialchars($course['title']) ?></h3>
                <p class="text-muted text-center"><?= htmlspecialchars($course['description']) ?></p>
                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Enrolled Students</b> <a class="float-end"><?= count($students) ?></a>
                    </li>
                    <li class="list-group-item">
                        <b>Materials</b> <a class="float-end"><?= count($materials) ?></a>
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Add Material</h3>
            </div>
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="upload_material" value="1">
                    <div class="mb-3">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Type</label>
                        <select name="type" class="form-select" id="matType">
                            <option value="pdf">PDF Document</option>
                            <option value="video">Video Link</option>
                            <option value="assignment">Assignment</option>
                        </select>
                    </div>
                    <div class="mb-3" id="fileInput">
                        <label>File (PDF)</label>
                        <input type="file" name="file" class="form-control">
                    </div>
                    <div class="mb-3 d-none" id="linkInput">
                        <label>Link / Content</label>
                        <input type="text" name="link" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Material</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card card-tabs">
            <div class="card-header p-0 pt-1 border-bottom-0">
                <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tabs-materials-tab" data-bs-toggle="pill" href="#tabs-materials" role="tab">Course Materials</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tabs-students-tab" data-bs-toggle="pill" href="#tabs-students" role="tab">Enrolled Students</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="custom-tabs-three-tabContent">
                    <div class="tab-pane fade show active" id="tabs-materials" role="tabpanel">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($materials as $m): ?>
                                <tr>
                                    <td><?= htmlspecialchars($m['title']) ?></td>
                                    <td><span class="badge bg-info"><?= strtoupper($m['type']) ?></span></td>
                                    <td><?= date('M d', strtotime($m['created_at'])) ?></td>
                                    <td>
                                        <a href="../../<?= htmlspecialchars($m['file_path']) ?>" target="_blank" class="btn btn-xs btn-default"><i class="bi bi-eye"></i> View</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="tabs-students" role="tabpanel">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Enrolled Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($students as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['name']) ?></td>
                                    <td><?= htmlspecialchars($s['email']) ?></td>
                                    <td><?= date('M d, Y', strtotime($s['enrolled_at'])) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('matType').addEventListener('change', function() {
    if(this.value === 'pdf' || this.value === 'assignment') {
        document.getElementById('fileInput').classList.remove('d-none');
        document.getElementById('linkInput').classList.add('d-none');
    } else {
        document.getElementById('fileInput').classList.add('d-none');
        document.getElementById('linkInput').classList.remove('d-none');
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>
