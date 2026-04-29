<?php
require_once '../../core/session.php';
checkRole('admin');
require_once '../../core/db.php';
require_once '../../includes/header.php';

// Add Course (Admin Override)

// Add Course (Admin Override)
if (isset($_POST['add_course'])) {
    $title = $_POST['title'];
    $code = $_POST['code'];
    $teacher_id = $_POST['teacher_id'];
    
    $stmt = $pdo->prepare("INSERT INTO courses (title, course_code, teacher_id) VALUES (?, ?, ?)");
    $stmt->execute([$title, $code, $teacher_id]);
    echo "<script>alert('Course Added'); window.location.href='courses.php';</script>";
}

// Fetch Courses with Teacher Name
$sql = "SELECT c.*, u.name as teacher_name FROM courses c LEFT JOIN users u ON c.teacher_id = u.id ORDER BY c.created_at DESC";
$courses = $pdo->query($sql)->fetchAll();

// Fetch Teachers for Dropdown
$teachers = $pdo->query("SELECT id, name FROM users WHERE role = 'teacher' AND is_active = 1")->fetchAll();

// Enrollment Stats for Chart
$enroll_stmt = $pdo->query("SELECT c.title, COUNT(e.id) as student_count FROM courses c LEFT JOIN enrollments e ON c.id = e.course_id GROUP BY c.id");
$enroll_data = $enroll_stmt->fetchAll();
$course_labels = [];
$student_counts = [];
foreach($enroll_data as $row) {
    $course_labels[] = $row['title'];
    $student_counts[] = $row['student_count'];
}

require_once '../../includes/visual_helper.php';
echo getChartScripts();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Manage Courses</h3></div>
            <div class="col-sm-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCourseModal">Add Course</button>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Title</th>
                            <th>Teacher</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($courses as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['course_code']) ?></td>
                            <td><?= htmlspecialchars($c['title']) ?></td>
                            <td><?= htmlspecialchars($c['teacher_name'] ?? 'Unassigned') ?></td>
                            <td>
                                <a href="#" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#assignModal<?= $c['id'] ?>">Re-Assign</a>
                                <a href="delete_course.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete course?')">Delete</a>
                            </td>
                        </tr>

                        <!-- Assign Modal -->
                        <div class="modal fade" id="assignModal<?= $c['id'] ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="post" action="assign_teacher.php" class="modal-content">
                                    <div class="modal-header"><h5 class="modal-title">Assign Teacher</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                    <div class="modal-body">
                                        <p>Assign teacher for: <strong><?= htmlspecialchars($c['title']) ?></strong></p>
                                        <select name="teacher_id" class="form-control">
                                            <?php foreach($teachers as $t): ?>
                                            <option value="<?= $t['id'] ?>" <?= $c['teacher_id'] == $t['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($t['name']) ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" name="course_id" value="<?= $c['id'] ?>">
                                    </div>
                                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Course Analytics Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-success">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar me-2"></i> Enrollment per Course</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="enrollmentChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('enrollmentChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($course_labels) ?>,
            datasets: [{
                label: 'Enrolled Students',
                data: <?= json_encode($student_counts) ?>,
                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
});
</script>

<!-- Add Modal -->
<div class="modal fade" id="addCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Course</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Title</label><input type="text" name="title" class="form-control" required></div>
                <div class="mb-3"><label>Code</label><input type="text" name="code" class="form-control" required></div>
                <div class="mb-3"><label>Teacher</label>
                    <select name="teacher_id" class="form-control">
                        <option value="">Select Teacher</option>
                        <?php foreach($teachers as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="add_course" value="1">
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
