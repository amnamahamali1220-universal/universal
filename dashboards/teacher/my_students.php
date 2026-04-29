<?php
require_once '../../core/session.php';
checkRole('teacher');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$teacher_id = $_SESSION['user_id'];

if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin') {
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.registration_no, a.assigned_date, t.name as teacher_col
        FROM users u
        JOIN teacher_student_assign a ON u.id = a.student_id
        JOIN users t ON a.teacher_id = t.id
        ORDER BY u.name ASC
    ");
    $stmt->execute();
} else {
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email, u.registration_no, a.assigned_date, '' as teacher_col 
        FROM users u
        JOIN teacher_student_assign a ON u.id = a.student_id
        WHERE a.teacher_id = ?
        ORDER BY u.name ASC
    ");
    $stmt->execute([$teacher_id]);
}
$students = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>My Assigned Students</h3></div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-outline card-success shadow-sm">
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Reg No</th>
                            <th>Name</th>
                            <th>Email</th>
                            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin') echo '<th>Assigned Teacher</th>'; ?>
                            <th>Assigned Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['registration_no']) ?></td>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'super_admin') echo '<td>'.htmlspecialchars($s['teacher_col']).'</td>'; ?>
                            <td><?= date('M d, Y', strtotime($s['assigned_date'])) ?></td>
                            <td>
                                <a href="student_performance.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-info">View Performance</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($students)): ?>
                            <tr><td colspan="5" class="text-center">No students assigned to you yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
