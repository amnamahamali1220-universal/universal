<?php
require_once '../../core/session.php';
checkRole('admin');
require_once '../../core/db.php';
require_once '../../includes/header.php';
require_once '../../includes/sidebar.php';

// Add Teacher
if (isset($_POST['add_teacher'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $identity = $_POST['identity']; // CNIC
    
    // Check if exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        echo "<script>alert('Email already exists');</script>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, identity_no, is_active) VALUES (?, ?, ?, 'teacher', ?, 1)");
        $stmt->execute([$name, $email, $password, $identity]);
        echo "<script>alert('Teacher Added'); window.location.href='teachers.php';</script>";
    }
}

// Fetch Teachers
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'teacher' ORDER BY created_at DESC");
$stmt->execute();
$teachers = $stmt->fetchAll();

// Status Stats for Chart
$status_stmt = $pdo->query("SELECT is_active, COUNT(*) as count FROM users WHERE role = 'teacher' GROUP BY is_active");
$status_data = $status_stmt->fetchAll();
$active_count = 0;
$inactive_count = 0;
foreach($status_data as $row) {
    if($row['is_active']) $active_count = $row['count'];
    else $inactive_count = $row['count'];
}

require_once '../../includes/visual_helper.php';
echo getChartScripts();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Manage Teachers</h3></div>
            <div class="col-sm-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">Add New Teacher</button>
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Identity No</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($teachers as $t): ?>
                        <tr>
                            <td><?= htmlspecialchars($t['name']) ?></td>
                            <td><?= htmlspecialchars($t['email']) ?></td>
                            <td><?= htmlspecialchars($t['identity_no']) ?></td>
                            <td><?= $t['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete_user.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Teacher Analytics Section -->
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card card-outline card-info">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-pie me-2"></i> Teacher Status Distribution</h3>
                    </div>
                    <div class="card-body">
                        <div style="max-height: 250px;">
                            <canvas id="statusDistChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('statusDistChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Active', 'Inactive'],
            datasets: [{
                data: [<?= $active_count ?>, <?= $inactive_count ?>],
                backgroundColor: ['#28a745', '#dc3545'],
                borderWidth: 1
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
});
</script>

<!-- Modal -->
<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Teacher</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                <div class="mb-3"><label>Identity No (CNIC)</label><input type="text" name="identity" class="form-control" required></div>
                <input type="hidden" name="add_teacher" value="1">
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
