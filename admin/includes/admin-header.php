<?php
/** Admin layout header. Expects optional $adminTitle and $activeNav. */
$adminTitle = $adminTitle ?? 'Dashboard';
$activeNav = $activeNav ?? '';
$admin = $admin ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($adminTitle) ?> — Admin</title>
    <link rel="icon" href="<?= e(base_url('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@600;700&display=swap">
    <link rel="stylesheet" href="<?= e(base_url('admin/assets/css/admin.css')) ?>">
</head>
<body>
    <header class="topbar">
        <div class="topbar__inner">
            <a class="brand" href="<?= e(base_url('admin/dashboard.php')) ?>">
                <span class="brand__mark" aria-hidden="true">S</span>
                <span class="brand__text">Sobur <small>Admin</small></span>
            </a>

            <?php if ($admin): ?>
                <nav class="mainnav" aria-label="Admin">
                    <a href="<?= e(base_url('admin/dashboard.php')) ?>" class="<?= $activeNav === 'dashboard' ? 'is-active' : '' ?>">Dashboard</a>
                    <a href="<?= e(base_url('admin/projects.php')) ?>" class="<?= $activeNav === 'projects' ? 'is-active' : '' ?>">Projects</a>
                    <a href="<?= e(base_url('admin/messages.php')) ?>" class="<?= $activeNav === 'messages' ? 'is-active' : '' ?>">Messages</a>
                </nav>
                <div class="topbar__user">
                    <span class="user-chip" title="Signed in">
                        <span class="user-dot" aria-hidden="true"></span><?= e($admin['username']) ?>
                    </span>
                    <a class="logout-btn" href="<?= e(base_url('admin/logout.php')) ?>">Log out</a>
                </div>
            <?php endif; ?>
        </div>
    </header>
    <main class="admin-main">
        <div class="admin-container">
            <?php if ($msg = flash_get('admin_ok')): ?>
                <div class="notice notice--ok" role="status"><?= e($msg) ?></div>
            <?php endif; ?>
            <?php if ($msg = flash_get('admin_err')): ?>
                <div class="notice notice--err" role="alert"><?= e($msg) ?></div>
            <?php endif; ?>
