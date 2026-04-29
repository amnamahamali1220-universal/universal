<?php
require_once '../../core/session.php';
checkRole('admin');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$success = '';
$error = '';

// Handle Assignment
if (isset($_POST['assign'])) {
    $teacher_id = $_POST['teacher_id'];
    $student_id = $_POST['student_id'];

    try {
        // Since student_id is UNIQUE, we use REPLACE INTO or INSERT ON DUPLICATE KEY UPDATE to ensure 1:N
        $stmt = $pdo->prepare("REPLACE INTO teacher_student_assign (teacher_id, student_id) VALUES (?, ?)");
        $stmt->execute([$teacher_id, $student_id]);
        $success = "Student assigned to teacher successfully.";
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Handle Unassign
if (isset($_GET['unassign'])) {
    $id = $_GET['unassign'];
    $pdo->prepare("DELETE FROM teacher_student_assign WHERE id = ?")->execute([$id]);
    $success = "Assignment removed.";
}

// Fetch Teachers and Students
$teachers = $pdo->query("SELECT id, name FROM users WHERE role = 'teacher' OR role IN (SELECT role_key FROM sys_roles WHERE role_key LIKE '%teacher%' OR role_key LIKE '%instructor%')")->fetchAll();
$students = $pdo->query("SELECT id, name, registration_no FROM users WHERE role = 'student'")->fetchAll();

// Fetch Current Assignments
$assignments = $pdo->query("
    SELECT a.id, t.name as teacher_name, s.name as student_name, s.registration_no, a.assigned_date 
    FROM teacher_student_assign a
    JOIN users t ON a.teacher_id = t.id
    JOIN users s ON a.student_id = s.id
    ORDER BY a.assigned_date DESC
")->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Assign Students to Teachers</h3></div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <?php if($success): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>
        <?php if($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card card-primary card-outline">
                    <div class="card-header"><h3 class="card-title">New Assignment</h3></div>
                    <form method="post" class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Select Teacher</label>
                            <select name="teacher_id" class="form-select select2" required>
                                <option value="">-- Choose Teacher --</option>
                                <?php foreach($teachers as $t): ?>
                                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Select Student</label>
                            <select name="student_id" class="form-select select2" required>
                                <option value="">-- Choose Student --</option>
                                <?php foreach($students as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['registration_no']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" name="assign" class="btn btn-primary w-100">Assign Student</button>
                    </form>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Existing Assignments</h3></div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Teacher</th>
                                    <th>Student</th>
                                    <th>Reg No</th>
                                    <th>Assigned At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($assignments as $a): ?>
                                <tr>
                                    <td><?= htmlspecialchars($a['teacher_name']) ?></td>
                                    <td><?= htmlspecialchars($a['student_name']) ?></td>
                                    <td><?= htmlspecialchars($a['registration_no']) ?></td>
                                    <td><?= date('M d, Y', strtotime($a['assigned_date'])) ?></td>
                                    <td>
                                        <a href="?unassign=<?= $a['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Remove this assignment?')">Unassign</a>
                                    </td>
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

<?php require_once '../../includes/footer.php'; ?>
