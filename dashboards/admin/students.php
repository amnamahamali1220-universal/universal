<?php
require_once '../../core/session.php';
checkRole('admin');
require_once '../../core/db.php';
require_once '../../includes/header.php';

// Add Student
if (isset($_POST['add_student'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $identity = $_POST['identity']; // CNIC
    $reg = $_POST['reg_no'];
    
    // Check if exists
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ? OR registration_no = ?");
    $check->execute([$email, $reg]);
    if ($check->rowCount() > 0) {
        echo "<script>alert('Email or Registration No already exists');</script>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, identity_no, registration_no, is_active) VALUES (?, ?, ?, 'student', ?, ?, 1)");
        $stmt->execute([$name, $email, $password, $identity, $reg]);
        
        require_once '../../includes/activity_logger.php';
        logActivity($pdo, $_SESSION['user_id'], "Added new student: " . $name . " (" . $reg . ")");
        
        echo "<script>alert('Student Added'); window.location.href='students.php';</script>";
    }
}

// Fetch Students
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC");
$stmt->execute();
$students = $stmt->fetchAll();

// Registration Stats for Chart
$stats_stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%b %Y') as month, COUNT(*) as count FROM users WHERE role = 'student' GROUP BY month ORDER BY created_at ASC");
$stats_data = $stats_stmt->fetchAll();
$months = [];
$counts = [];
foreach($stats_data as $row) {
    $months[] = $row['month'];
    $counts[] = $row['count'];
}

require_once '../../includes/visual_helper.php';
echo getChartScripts();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Manage Students</h3></div>
            <div class="col-sm-6 text-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStudentModal">Add New Student</button>
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
                            <th>Reg No</th>
                            <th>Email</th>
                            <th>Identity No</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($students as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['name']) ?></td>
                            <td><?= htmlspecialchars($s['registration_no']) ?></td>
                            <td><?= htmlspecialchars($s['email']) ?></td>
                            <td><?= htmlspecialchars($s['identity_no']) ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete_user.php?id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Student Analytics Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card card-outline card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-line me-2"></i> Student Registration Trends</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="regTrendChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('regTrendChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($months) ?>,
            datasets: [{
                label: 'New Registrations',
                data: <?= json_encode($counts) ?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.4
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

<!-- Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="post" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Add Student</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label>Registration No</label><input type="text" name="reg_no" class="form-control" required></div>
                <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                <div class="mb-3"><label>Identity No (CNIC)</label><input type="text" name="identity" class="form-control" required></div>
                <input type="hidden" name="add_student" value="1">
            </div>
            <div class="modal-footer"><button type="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
