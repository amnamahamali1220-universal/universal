<?php
require_once '../../core/session.php';
checkRole('admin');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$user_id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die('<div class="alert alert-danger m-5">User Not Found.</div>');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $identity_no = $_POST['identity_no'];
    $registration_no = $_POST['registration_no'];
    $is_active = $_POST['is_active'];

    try {
        $update = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, identity_no = ?, registration_no = ?, is_active = ? WHERE id = ?");
        $update->execute([$name, $email, $role, $identity_no, $registration_no, $is_active, $user_id]);
        $success = "User updated successfully.";
        // Refresh data
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (Exception $e) {
        $error = "Error updating user: " . $e->getMessage();
    }
}

$roles = $pdo->query("SELECT * FROM sys_roles")->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Edit User</h3></div>
            <div class="col-sm-6 text-end">
                <a href="<?= ($user['role'] == 'student') ? 'students.php' : 'teachers.php' ?>" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body">
                <?php if ($error): ?> <div class="alert alert-danger"><?= $error ?></div> <?php endif; ?>
                <?php if ($success): ?> <div class="alert alert-success"><?= $success ?></div> <?php endif; ?>

                <form method="POST" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= $r['role_key'] ?>" <?= ($user['role'] == $r['role_key']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['role_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Identity No (CNIC)</label>
                        <input type="text" name="identity_no" class="form-control" value="<?= htmlspecialchars($user['identity_no']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Registration No</label>
                        <input type="text" name="registration_no" class="form-control" value="<?= htmlspecialchars($user['registration_no']) ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Account Status</label>
                        <select name="is_active" class="form-select">
                            <option value="1" <?= ($user['is_active'] == 1) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= ($user['is_active'] == 0) ? 'selected' : '' ?>>Suspended</option>
                        </select>
                    </div>
                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary">Update User Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
