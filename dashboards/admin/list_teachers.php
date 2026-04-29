<?php
require_once '../../core/session.php';
checkRole('admin');
require_once '../../core/db.php';
require_once '../../includes/header.php';

// Fetch Teachers
$stmt = $pdo->prepare("SELECT * FROM users WHERE role = 'teacher' ORDER BY created_at DESC");
$stmt->execute();
$teachers = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Manage Teachers</h3></div>
            <div class="col-sm-6 text-end">
                <a href="add_teacher.php" class="btn btn-primary">Add New Teacher</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card">
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
                        <?php if (empty($teachers)): ?>
                        <tr>
                            <td colspan="5" class="text-center">No teachers found.</td>
                        </tr>
                        <?php endif; ?>
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
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
