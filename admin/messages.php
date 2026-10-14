<?php
/** Admin — contact messages. */
require_once __DIR__ . '/includes/auth.php';
$admin = require_admin();

if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        flash_set('admin_err', 'Your session expired. Please try again.');
        redirect(base_url('admin/messages.php'));
    }

    $action = (string) ($_POST['action'] ?? '');
    $ids = $_POST['ids'] ?? [];
    if (!is_array($ids)) {
        $ids = [$ids];
    }
    $ids = array_map('intval', $ids);
    $ids = array_filter($ids, static fn ($v) => $v > 0);

    if ($ids) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        if ($action === 'read') {
            db()->prepare("UPDATE messages SET is_read = 1 WHERE id IN ($placeholders)")->execute($ids);
            flash_set('admin_ok', count($ids) . ' message(s) marked as read.');
        } elseif ($action === 'unread') {
            db()->prepare("UPDATE messages SET is_read = 0 WHERE id IN ($placeholders)")->execute($ids);
            flash_set('admin_ok', count($ids) . ' message(s) marked as unread.');
        } elseif ($action === 'delete') {
            db()->prepare("DELETE FROM messages WHERE id IN ($placeholders)")->execute($ids);
            flash_set('admin_ok', count($ids) . ' message(s) deleted.');
        }
    }
    redirect(base_url('admin/messages.php'));
}

$messages = db()->query('SELECT * FROM messages ORDER BY created_at DESC, id DESC')->fetchAll();

$adminTitle = 'Messages';
$activeNav = 'messages';
require __DIR__ . '/includes/admin-header.php';
?>
<h1 class="page-title">Messages</h1>
<p class="page-sub">Newest first. <?= count($messages) ?> total.</p>

<?php if ($messages): ?>
<form method="post" action="<?= e(base_url('admin/messages.php')) ?>" class="bulk-form">
    <?= csrf_field() ?>
    <div class="bulk-bar">
        <div class="row-actions">
            <button type="submit" class="link-btn" name="action" value="read">Mark selected as read</button>
            <button type="submit" class="link-btn" name="action" value="unread">Mark unread</button>
            <button type="submit" class="link-btn link-btn--danger" name="action" value="delete"
                    onclick="return confirm('Delete the selected messages?');">Delete selected</button>
        </div>
    </div>

    <div class="message-list">
        <?php foreach ($messages as $m): ?>
            <article class="message-card <?= (int) $m['is_read'] === 0 ? 'is-unread' : '' ?>">
                <input type="checkbox" name="ids[]" value="<?= (int) $m['id'] ?>" aria-label="Select message from <?= e($m['name']) ?>">
                <div class="message-body">
                    <h2 class="message-subject">
                        <?= e($m['name']) ?>
                        <?php if ((int) $m['is_read'] === 0): ?><span class="pill pill--new">New</span><?php endif; ?>
                    </h2>
                    <p class="message-meta">
                        <a href="mailto:<?= e($m['email']) ?>"><?= e($m['email']) ?></a>
                        &middot; <?= e($m['created_at']) ?> UTC
                        <?php if ($m['ip_address']): ?>&middot; <?= e($m['ip_address']) ?><?php endif; ?>
                    </p>
                    <p class="message-text"><?= nl2br(e($m['message'])) ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</form>
<?php else: ?>
    <p class="page-sub">No messages yet.</p>
<?php endif; ?>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
