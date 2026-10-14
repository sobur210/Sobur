<?php
/** Admin dashboard. */
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();

$projectCount = (int) db()->query('SELECT COUNT(*) FROM projects')->fetchColumn();
$featured     = db()->query('SELECT title FROM projects WHERE is_featured = 1 LIMIT 1')->fetchColumn();
$messageCount = (int) db()->query('SELECT COUNT(*) FROM messages')->fetchColumn();
$unread       = (int) db()->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$recent       = db()->query('SELECT id, name, email, created_at, is_read FROM messages ORDER BY created_at DESC LIMIT 5')->fetchAll();

$adminTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/includes/admin-header.php';
?>
<h1 class="page-title">Dashboard</h1>
<p class="page-sub">Welcome back, <?= e($admin['username']) ?>.</p>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-num"><?= $projectCount ?></span>
        <span class="stat-label">Projects</span>
        <a href="<?= e(base_url('admin/projects.php')) ?>">Manage projects &rarr;</a>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= $messageCount ?></span>
        <span class="stat-label">Messages</span>
        <a href="<?= e(base_url('admin/messages.php')) ?>">View messages &rarr;</a>
    </div>
    <div class="stat-card">
        <span class="stat-num"><?= $unread ?></span>
        <span class="stat-label">Unread</span>
        <a href="<?= e(base_url('admin/messages.php')) ?>">Open inbox &rarr;</a>
    </div>
</div>

<h2 class="page-h2">Recent messages</h2>
<?php if ($recent): ?>
    <table class="admin-table">
        <thead>
            <tr><th>From</th><th>Email</th><th>Received</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($recent as $row): ?>
            <tr>
                <td><strong><?= e($row['name']) ?></strong></td>
                <td><?= e($row['email']) ?></td>
                <td><?= e($row['created_at']) ?></td>
                <td><?= $row['is_read'] ? 'Read' : '<span class="pill pill--new">New</span>' ?></td>
                <td><a href="<?= e(base_url('admin/messages.php')) ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="page-sub">No messages yet.</p>
<?php endif; ?>

<p class="page-sub" style="margin-top:24px;">
    Featured on the homepage:
    <strong><?= $featured ? e($featured) : 'none selected' ?></strong>
</p>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
