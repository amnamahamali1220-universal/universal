<?php
require_once '../../includes/header.php';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $settingsToUpdate = [
        'system_name' => $_POST['system_name'] ?? '',
        'contact_email' => $_POST['contact_email'] ?? '',
        'smtp_host' => $_POST['smtp_host'] ?? '',
        'smtp_port' => $_POST['smtp_port'] ?? '',
        'smtp_user' => $_POST['smtp_user'] ?? '',
        'smtp_pass' => $_POST['smtp_pass'] ?? '',
        'theme_color' => $_POST['theme_color'] ?? ''
    ];

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        foreach ($settingsToUpdate as $key => $val) {
            $stmt->execute([$key, $val, $val]);
        }
        $pdo->commit();
        $successMsg = "Global settings updated successfully.";
        
        // Refresh settings array for the current page load
        foreach ($settingsToUpdate as $key => $val) {
            $settings[$key] = $val;
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $errorMsg = "Failed to update settings: " . $e->getMessage();
    }
}
?>

<div class="row justify-content-center mt-4">
    <div class="col-lg-8">
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
            <div class="card-header bg-primary text-white rounded-top-4 py-3 d-flex align-items-center">
                <i class="fas fa-cogs fs-4 me-3"></i>
                <h4 class="mb-0">Global System Settings</h4>
            </div>
            <div class="card-body p-4">
                <form action="" method="POST">
                    <h5 class="text-secondary mb-3 border-bottom pb-2">General Info</h5>
                    <div class="mb-3">
                        <label class="form-label fw-bold">System Name (Site Title)</label>
                        <input type="text" name="system_name" class="form-control form-control-lg" value="<?= htmlspecialchars($settings['system_name'] ?? 'Universal CMS') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Support / Contact Email</label>
                        <input type="email" name="contact_email" class="form-control" value="<?= htmlspecialchars($settings['contact_email'] ?? 'support@example.com') ?>" required>
                    </div>

                    <h5 class="text-secondary mb-3 mt-4 border-bottom pb-2">SMTP Configuration (Emailing)</h5>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">SMTP Host</label>
                            <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($settings['smtp_host'] ?? 'smtp.example.com') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">SMTP Port</label>
                            <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">SMTP Username</label>
                            <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($settings['smtp_user'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">SMTP Password</label>
                            <input type="password" name="smtp_pass" class="form-control" value="<?= htmlspecialchars($settings['smtp_pass'] ?? '') ?>">
                        </div>
                    </div>

                    <h5 class="text-secondary mb-3 mt-4 border-bottom pb-2">Appearance</h5>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Primary Theme Color (Hex)</label>
                        <div class="d-flex align-items-center">
                            <input type="color" name="theme_color" class="form-control form-control-color" value="<?= htmlspecialchars($settings['theme_color'] ?? '#0d6efd') ?>" title="Choose your color">
                            <span class="ms-3 text-muted">Used for emails and system highlights</span>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" name="update_settings" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                            <i class="fas fa-save me-2"></i> Save Configuration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
