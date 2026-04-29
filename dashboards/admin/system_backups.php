<?php
require_once '../../includes/header.php';
require_once '../../core/backup_helper.php';

$backupDir = APP_ROOT . '/uploads/backups/';
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

// Handle backup generation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_backup'])) {
    $result = generateDatabaseBackup();
    if ($result['success']) {
        $successMsg = "Database backup generated successfully: " . $result['file'];
    } else {
        $errorMsg = "Failed to generate backup. Error: " . $result['error'];
    }
}

// Handle backup deletion
if (isset($_GET['delete'])) {
    $fileToDelete = basename($_GET['delete']);
    $pathToDelete = $backupDir . $fileToDelete;
    if (file_exists($pathToDelete) && pathinfo($pathToDelete, PATHINFO_EXTENSION) === 'sql') {
        unlink($pathToDelete);
        $successMsg = "Backup deleted successfully.";
    }
}

// Handle backup download
if (isset($_GET['download'])) {
    $fileToDownload = basename($_GET['download']);
    $pathToDownload = $backupDir . $fileToDownload;
    if (file_exists($pathToDownload) && pathinfo($pathToDownload, PATHINFO_EXTENSION) === 'sql') {
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $fileToDownload . '"');
        header('Content-Length: ' . filesize($pathToDownload));
        readfile($pathToDownload);
        exit;
    }
}

// List all backups
$files = array_diff(scandir($backupDir), array('.', '..'));
$backups = [];
foreach ($files as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
        $backups[] = [
            'name' => $file,
            'size' => round(filesize($backupDir . $file) / 1024, 2) . ' KB',
            'time' => date("Y-m-d H:i:s", filemtime($backupDir . $file))
        ];
    }
}
// Sort by time descending
usort($backups, function($a, $b) {
    return strtotime($b['time']) - strtotime($a['time']);
});

?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-10">
        <?php if(isset($successMsg)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($successMsg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if(isset($errorMsg)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($errorMsg) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow border-0 rounded-4">
            <div class="card-header bg-dark text-white rounded-top-4 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-database fs-4 me-2"></i>
                    <h4 class="mb-0 d-inline-block">System Backups</h4>
                </div>
                <form method="POST" action="">
                    <button type="submit" name="generate_backup" class="btn btn-primary rounded-pill shadow-sm">
                        <i class="fas fa-plus-circle me-1"></i> Generate New Backup
                    </button>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Backup File Name</th>
                            <th>Created On</th>
                            <th>File Size</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($backups)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-folder-open fs-2 mb-2"></i>
                                <p class="mb-0">No backups found.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($backups as $b): ?>
                            <tr>
                                <td class="ps-4">
                                    <i class="fas fa-file-code text-primary me-2"></i>
                                    <strong><?= htmlspecialchars($b['name']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($b['time']) ?></td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($b['size']) ?></span></td>
                                <td class="text-end pe-4">
                                    <a href="?download=<?= urlencode($b['name']) ?>" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm" title="Download">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="?delete=<?= urlencode($b['name']) ?>" class="btn btn-sm btn-danger rounded-pill px-3 shadow-sm ms-1" onclick="return confirm('Are you sure you want to delete this backup?');" title="Delete">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
