<?php
require_once '../../core/session.php';
// Check if user is admin or super_admin
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'super_admin') {
    die("Access Denied");
}
require_once '../../core/db.php';
require_once '../../includes/header.php';

// Filtering
$whereClause = "1=1";
$params = [];

if (!empty($_GET['user_id'])) {
    $whereClause .= " AND a.user_id = ?";
    $params[] = $_GET['user_id'];
}
if (!empty($_GET['action'])) {
    $whereClause .= " AND a.action LIKE ?";
    $params[] = "%" . $_GET['action'] . "%";
}
if (!empty($_GET['date_from'])) {
    $whereClause .= " AND DATE(a.timestamp) >= ?";
    $params[] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $whereClause .= " AND DATE(a.timestamp) <= ?";
    $params[] = $_GET['date_to'];
}

$sql = "SELECT a.*, u.name as user_name, u.role as user_role 
        FROM activity_logs a 
        LEFT JOIN users u ON a.user_id = u.id 
        WHERE {$whereClause} 
        ORDER BY a.timestamp DESC LIMIT 500";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6"><h3>Activity Logs</h3></div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <!-- Filter Form -->
        <div class="card mb-3">
            <div class="card-body">
                <form method="get" class="row gx-3 gy-2 align-items-center">
                    <div class="col-sm-3">
                        <label class="visually-hidden">User ID</label>
                        <input type="text" name="user_id" class="form-control" placeholder="User ID" value="<?= htmlspecialchars($_GET['user_id'] ?? '') ?>">
                    </div>
                    <div class="col-sm-3">
                        <label class="visually-hidden">Action</label>
                        <input type="text" name="action" class="form-control" placeholder="Action keyword" value="<?= htmlspecialchars($_GET['action'] ?? '') ?>">
                    </div>
                    <div class="col-sm-2">
                        <label class="visually-hidden">Date From</label>
                        <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">
                    </div>
                    <div class="col-sm-2">
                        <label class="visually-hidden">Date To</label>
                        <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="card mb-4">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Timestamp</th>
                            <th>User Name (Role)</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($logs) > 0): ?>
                            <?php foreach($logs as $log): ?>
                            <tr>
                                <td><?= htmlspecialchars($log['id']) ?></td>
                                <td><?= htmlspecialchars($log['timestamp']) ?></td>
                                <td><?= htmlspecialchars($log['user_name'] . ' (' . $log['user_role'] . ')') ?></td>
                                <td><?= htmlspecialchars($log['action']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center">No logs found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
