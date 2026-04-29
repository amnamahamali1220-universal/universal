<?php
require_once '../../core/session.php';
checkRole('admin');
require_once '../../core/db.php';
require_once '../../includes/header.php';

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $identity = trim($_POST['identity']); // CNIC

    if ($name && $email && $password && $identity) {
        // Check if email already exists
        $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);
        if ($check->rowCount() > 0) {
            $error = "Email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, identity_no, is_active) VALUES (?, ?, ?, 'teacher', ?, 1)");
            if ($stmt->execute([$name, $email, $hashed_password, $identity])) {
                $msg = "Teacher added successfully.";
            } else {
                $error = "Failed to add teacher.";
            }
        }
    } else {
        $error = "All fields are required.";
    }
}
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Add New Teacher</h3></div>
            <div class="col-sm-6 text-end">
                <a href="list_teachers.php" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <form method="post">
                        <div class="card-body">
                            <?php if($msg): ?>
                                <div class="alert alert-success"><?= $msg ?></div>
                            <?php endif; ?>
                            <?php if($error): ?>
                                <div class="alert alert-danger"><?= $error ?></div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="Enter teacher's full name">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" required placeholder="Enter unique email">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required placeholder="Enter login password">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Identity No (CNIC/Passport)</label>
                                <input type="text" name="identity" class="form-control" required placeholder="Enter identification number">
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <button type="submit" class="btn btn-primary px-5">Add Teacher</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
