<?php
require_once __DIR__ . '/../core/session.php';

// 1. Fetch System Settings
$settings = [];
$stmt = $pdo->query("SELECT * FROM system_settings");
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// 2. Identify Current Page & Security Check
$current_url = substr($_SERVER['SCRIPT_NAME'], strlen('/universal/')); // Adjust offset
// Clean URL for DB matching (assuming DB stores relative paths)
$db_url_match = $current_url; 
// If your script is in a folder, the DB url should match "dashboards/super_admin/file.php"

// Fetch Page Info
$pageStmt = $pdo->prepare("SELECT * FROM sys_pages WHERE page_url LIKE ? LIMIT 1");
$pageStmt->execute(["%$current_url%"]); 
$currentPageData = $pageStmt->fetch();

$pageTitle = $currentPageData['page_name'] ?? 'Dashboard';
$pageId = $currentPageData['id'] ?? 0;

// 3. Security Access Check (The Gatekeeper)
if ($pageId > 0) {
    $accessStmt = $pdo->prepare("SELECT * FROM role_access WHERE role_key = ? AND page_id = ?");
    $accessStmt->execute([$_SESSION['role'], $pageId]);
    if ($accessStmt->rowCount() == 0) {
        die('<div class="alert alert-danger m-5">⛔ Access Denied: You do not have permission to view this page.</div>');
    }
}

// 4. Breadcrumb Logic (Recursive Upwards)
$breadcrumbs = [];
if ($currentPageData) {
    $crumbId = $currentPageData['id'];
    while($crumbId != 0) {
        $crumbStmt = $pdo->prepare("SELECT id, parent_id, page_name, page_url FROM sys_pages WHERE id = ?");
        $crumbStmt->execute([$crumbId]);
        $crumb = $crumbStmt->fetch();
        array_unshift($breadcrumbs, $crumb); // Add to beginning
        $crumbId = $crumb['parent_id'];
    }
}

// 5. Unread Notifications Fetch
$unread_notifications = [];
if (isset($_SESSION['user_id'])) {
    $notifStmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 5");
    $notifStmt->execute([$_SESSION['user_id']]);
    $unread_notifications = $notifStmt->fetchAll();
    
    // Total count for badge
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $countStmt->execute([$_SESSION['user_id']]);
    $unread_count = $countStmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | <?= htmlspecialchars($settings['system_name'] ?? 'Universal CMS') ?></title>
    
    <script>
        // Immediately check local storage to prevent "White Flash"
        const storedTheme = localStorage.getItem('theme');
        if (storedTheme) {
            document.documentElement.setAttribute('data-bs-theme', storedTheme);
        } else {
            // Default to system preference if no choice made
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', systemTheme);
        }
    </script>

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/bootstrap-icons.css" />
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/adminlte.min.css" />
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    
    <style> 
        .app-brand-logo { height: 30px; width: auto; } 
        .user-image { width: 30px; height: 30px; object-fit: cover; }
    </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav">
                <li class="nav-item"> <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button"><i class="bi bi-list"></i></a> </li>
                <li class="nav-item d-none d-md-block"> <a href="#" class="nav-link"><?= $pageTitle ?></a> </li>
            </ul>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <button class="btn btn-link nav-link" id="theme-toggle" type="button">
                        <i class="bi bi-sun-fill" id="theme-icon"></i>
                    </button>
                </li>
                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                        <i class="bi bi-bell"></i>
                        <?php if(!empty($unread_count) && $unread_count > 0): ?>
                            <span class="badge text-bg-warning position-absolute top-0 start-100 translate-middle rounded-pill"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end shadow border-0">
                        <span class="dropdown-header text-start fw-bold"><?= $unread_count ?? 0 ?> Unread Notifications</span>
                        <div class="dropdown-divider"></div>
                        <?php if(!empty($unread_notifications)): ?>
                            <?php foreach($unread_notifications as $n): ?>
                            <a href="<?= BASE_URL . $n['link'] ?>" class="dropdown-item">
                                <i class="bi bi-info-circle me-2 text-primary"></i> <?= htmlspecialchars($n['message']) ?>
                                <span class="float-end text-muted text-sm"><small><?= date('M d, H:i', strtotime($n['created_at'])) ?></small></span>
                            </a>
                            <div class="dropdown-divider"></div>
                            <?php endforeach; ?>
                            <div class="text-center p-2"><a href="#" class="text-muted text-decoration-none">Mark all as read</a></div>
                        <?php else: ?>
                            <a href="#" class="dropdown-item text-muted text-center py-3">No new notifications</a>
                        <?php endif; ?>
                    </div>
                </li>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <img src="<?= !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : BASE_URL.'assets/img/avatar.png' ?>" class="user-image rounded-circle shadow" alt="User Image">
                        <span class="d-none d-md-inline ms-1"><?= htmlspecialchars($_SESSION['name']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                        <li class="user-header text-bg-primary">
                            <img src="<?= !empty($_SESSION['avatar']) ? $_SESSION['avatar'] : BASE_URL.'assets/img/avatar.png' ?>" class="rounded-circle shadow" alt="User Image">
                            <p>
                                <?= htmlspecialchars($_SESSION['name']) ?>
                                <small><?= ucfirst(str_replace('_', ' ', $_SESSION['role'])) ?></small>
                            </p>
                        </li>
                        <li class="user-footer"> 
                            <a href="<?= BASE_URL ?>profile.php" class="btn btn-default btn-flat">Profile</a>
                            <a href="<?= BASE_URL ?>logout.php" class="btn btn-default btn-flat float-end">Sign out</a> 
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
    
    <?php include __DIR__ . '/sidebar.php'; ?>
    
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0"><?= $pageTitle ?></h3></div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">Home</a></li>
                            <?php foreach($breadcrumbs as $b): ?>
                                <li class="breadcrumb-item <?= ($b['id'] == $pageId) ? 'active' : '' ?>">
                                    <?= htmlspecialchars($b['page_name']) ?>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">