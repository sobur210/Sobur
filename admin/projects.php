<?php
/** Admin — list, reorder, feature, delete projects. */
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();

if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash_set('admin_err', 'Your session expired. Please try again.');
        redirect(base_url('admin/projects.php'));
    }

    $action = (string) ($_POST['action'] ?? '');
    $id = (int) ($_POST['id'] ?? 0);

    if ($id > 0 && $action === 'delete') {
        db()->prepare('DELETE FROM projects WHERE id = ?')->execute([$id]);
        flash_set('admin_ok', 'Project deleted.');
    } elseif ($id > 0 && $action === 'feature') {
        db()->prepare('UPDATE projects SET is_featured = 0')->execute();
        db()->prepare('UPDATE projects SET is_featured = 1 WHERE id = ?')->execute([$id]);
        flash_set('admin_ok', 'Featured project updated.');
    } elseif ($id > 0 && $action === 'unfeature') {
        db()->prepare('UPDATE projects SET is_featured = 0 WHERE id = ?')->execute([$id]);
        flash_set('admin_ok', 'Featured project cleared.');
    } elseif ($id > 0 && ($action === 'up' || $action === 'down')) {
        $stmt = db()->prepare('SELECT id, sort_order FROM projects WHERE id = ?');
        $stmt->execute([$id]);
        $current = $stmt->fetch();
        if ($current) {
            $cmp = $action === 'up' ? '<' : '>';
            $order = $action === 'up' ? 'DESC' : 'ASC';
            $neighbourStmt = db()->prepare(
                "SELECT id, sort_order FROM projects WHERE sort_order $cmp ? ORDER BY sort_order $order, id $order LIMIT 1"
            );
            $neighbourStmt->execute([$current['sort_order']]);
            $neighbour = $neighbourStmt->fetch();
            if ($neighbour) {
                $a = (int) $current['sort_order'];
                $b = (int) $neighbour['sort_order'];
                if ($a === $b) {
                    $b = $action === 'up' ? $a - 1 : $a + 1;
                }
                $upd = db()->prepare('UPDATE projects SET sort_order = ? WHERE id = ?');
                $upd->execute([$b, $current['id']]);
                $upd->execute([$a, $neighbour['id']]);
            }
        }
    }
    redirect(base_url('admin/projects.php'));
}

$projects = get_projects();

$adminTitle = 'Projects';
$activeNav = 'projects';
require __DIR__ . '/includes/admin-header.php';
?>
<div class="page-head">
    <div>
        <h1 class="page-title">Projects</h1>
        <p class="page-sub">These appear on the homepage, in this order.</p>
    </div>
    <a class="btn btn--primary btn--sm" href="<?= e(base_url('admin/project-edit.php')) ?>">Add project</a>
</div>

<?php if ($projects): ?>
<table class="admin-table">
    <thead>
        <tr><th style="width:70px;">Order</th><th>Title</th><th>Category</th><th>Featured</th><th style="width:220px;">Actions</th></tr>
    </thead>
    <tbody>
    <?php foreach ($projects as $p): ?>
        <tr>
            <td>
                <div class="order-controls">
                    <form method="post" action="<?= e(base_url('admin/projects.php')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="up">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" class="icon-btn" aria-label="Move <?= e($p['title']) ?> up">&uarr;</button>
                    </form>
                    <form method="post" action="<?= e(base_url('admin/projects.php')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="down">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" class="icon-btn" aria-label="Move <?= e($p['title']) ?> down">&darr;</button>
                    </form>
                </div>
            </td>
            <td>
                <strong><?= e($p['title']) ?></strong><br>
                <span class="muted">/<?= e($p['slug']) ?></span>
            </td>
            <td><?= e($p['category']) ?></td>
            <td>
                <?php if ((int) $p['is_featured'] === 1): ?>
                    <span class="pill pill--ok">Featured</span>
                <?php else: ?>
                    <form method="post" action="<?= e(base_url('admin/projects.php')) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="feature">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" class="link-btn">Make featured</button>
                    </form>
                <?php endif; ?>
            </td>
            <td>
                <div class="row-actions">
                    <a href="<?= e(base_url('admin/project-edit.php?id=' . (int) $p['id'])) ?>">Edit</a>
                    <form method="post" action="<?= e(base_url('admin/projects.php')) ?>" onsubmit="return confirm('Delete this project?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" class="link-btn link-btn--danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
    <p class="page-sub">No projects yet. <a href="<?= e(base_url('admin/project-edit.php')) ?>">Add your first project</a>.</p>
<?php endif; ?>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
