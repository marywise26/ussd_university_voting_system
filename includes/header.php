<?php
$page_title = $page_title ?? APP_NAME;
$flash = flash_message();
$isLoggedIn = is_logged_in();
$isAdmin = $isLoggedIn && (($_SESSION['role'] ?? '') === 'admin');

$currentPath = trim(str_replace('\\', '/', parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? ''), '/');

if (!function_exists('nav_active')) {
    function nav_active(string $needle, string $currentPath): string
    {
        return str_contains($currentPath, trim($needle, '/')) ? 'active' : '';
    }
}

$cssPath = __DIR__ . '/../assets/css/style.css';
$cssVersion = file_exists($cssPath) ? filemtime($cssPath) : time();

$faviconFile = 'assets/images/Mzumbe_University_Logo.png';
$faviconPath = __DIR__ . '/../assets/images/Mzumbe_University_Logo.png';
$faviconVersion = file_exists($faviconPath) ? filemtime($faviconPath) : time();
?>
<!DOCTYPE html>
<html lang="en">
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= e(APP_NAME) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= e(app_url($faviconFile) . '?v=' . $faviconVersion) ?>">
    <link rel="shortcut icon" type="image/png" href="<?= e(app_url($faviconFile) . '?v=' . $faviconVersion) ?>">
    <link rel="apple-touch-icon" href="<?= e(app_url($faviconFile) . '?v=' . $faviconVersion) ?>">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">

    <link rel="stylesheet" href="<?= e(app_url('assets/css/style.css') . '?v=' . $cssVersion) ?>">
</head>

<?php if ($isLoggedIn): ?>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed">
<div class="wrapper">
    <nav class="main-header navbar navbar-expand navbar-white navbar-light border-bottom-0 app-navbar">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                    <i class="fas fa-bars"></i>
                </a>
            </li>

            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= e(app_url('dashboard.php')) ?>" class="nav-link">Dashboard</a>
            </li>
        </ul>

        <ul class="navbar-nav ml-auto align-items-center">
            <li class="nav-item d-none d-md-inline-block mr-3 text-muted small">
                <?= e($_SESSION['full_name'] ?? 'User') ?> · <?= e(ucfirst($_SESSION['role'] ?? 'voter')) ?>
            </li>

            <li class="nav-item">
                <a class="btn btn-sm btn-outline-dark rounded-pill px-3" href="<?= e(app_url('logout.php')) ?>">
                    <i class="fas fa-arrow-right-from-bracket mr-1"></i> Logout
                </a>
            </li>
        </ul>
    </nav>

    <aside class="main-sidebar sidebar-dark-primary elevation-4 app-sidebar">
        <a href="<?= e(app_url('dashboard.php')) ?>" class="brand-link app-brand">
            <span class="brand-badge">MU</span>
            <span class="brand-text font-weight-light">MUSO</span>
        </a>

        <div class="sidebar">
            <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
                <div class="image">
                    <div class="avatar-circle">
                        <?= e(strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 1))) ?>
                    </div>
                </div>

                <div class="info">
                    <a href="<?= e(app_url('dashboard.php')) ?>" class="d-block">
                        <?= e($_SESSION['full_name'] ?? 'User') ?>
                    </a>
                    <span class="text-muted small">
                        <?= $isAdmin ? 'Election Administrator' : 'Student Voter' ?>
                    </span>
                </div>
            </div>

            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column nav-compact" data-widget="treeview" role="menu">
                    <?php if ($isAdmin): ?>
                        <li class="nav-item">
                            <a href="<?= e(app_url('admin/dashboard.php')) ?>" class="nav-link <?= nav_active('admin/dashboard.php', $currentPath) ?>">
                                <i class="nav-icon fas fa-gauge-high"></i>
                                <p>Overview</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= e(app_url('admin/students.php')) ?>" class="nav-link <?= nav_active('admin/students.php', $currentPath) ?>">
                                <i class="nav-icon fas fa-user-graduate"></i>
                                <p>Students</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= e(app_url('admin/elections.php')) ?>" class="nav-link <?= nav_active('admin/elections.php', $currentPath) ?>">
                                <i class="nav-icon fas fa-calendar-check"></i>
                                <p>Elections</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= e(app_url('admin/positions.php')) ?>" class="nav-link <?= nav_active('admin/positions.php', $currentPath) ?>">
                                <i class="nav-icon fas fa-layer-group"></i>
                                <p>Positions</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= e(app_url('admin/candidate_applications.php')) ?>" class="nav-link <?= nav_active('admin/candidate_applications.php', $currentPath) ?>">
                                <i class="nav-icon fas fa-user-check"></i>
                                <p>Candidate Vetting</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= e(app_url('admin/results.php')) ?>" class="nav-link <?= nav_active('admin/results.php', $currentPath) ?>">
                                <i class="nav-icon fas fa-chart-column"></i>
                                <p>Results</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= e(app_url('admin/audit_logs.php')) ?>" class="nav-link <?= nav_active('admin/audit_logs.php', $currentPath) ?>">
                                <i class="nav-icon fas fa-shield-halved"></i>
                                <p>Audit Logs</p>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="<?= e(app_url('voter/dashboard.php')) ?>" class="nav-link <?= nav_active('voter/dashboard.php', $currentPath) ?>">
                                <i class="nav-icon fas fa-house"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= e(app_url('voter/apply_candidate.php')) ?>" class="nav-link <?= nav_active('voter/apply_candidate.php', $currentPath) ?>">
                                <i class="nav-icon fas fa-file-signature"></i>
                                <p>Apply as Candidate</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= e(app_url('voter/application_status.php')) ?>" class="nav-link <?= nav_active('voter/application_status.php', $currentPath) ?>">
                                <i class="nav-icon fas fa-list-check"></i>
                                <p>Application Status</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= e(app_url('voter/ballot.php')) ?>" class="nav-link <?= nav_active('voter/ballot.php', $currentPath) ?>">
                                <i class="nav-icon fas fa-square-poll-vertical"></i>
                                <p>Ballot</p>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </aside>

    <div class="content-wrapper app-content">
        <section class="content-header pb-0">
            <div class="container-fluid">
                <div class="page-heading">
                    <div>
                        <p class="eyebrow mb-1">
                            <?= $isAdmin ? 'Administration Console' : 'Student Portal' ?>
                        </p>
                        <h1 class="m-0"><?= e($page_title) ?></h1>
                    </div>
                </div>

                <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?> mt-3 mb-0">
                        <?= e($flash['message']) ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="content py-3">
            <div class="container-fluid">

<?php else: ?>
<body class="hold-transition login-page app-auth-page">
    <div class="auth-shell">
<?php endif; ?>