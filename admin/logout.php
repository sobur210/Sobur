<?php
/** Admin logout. */
require_once __DIR__ . '/includes/auth.php';

if (is_post()) {
    if (csrf_verify($_POST['csrf_token'] ?? null)) {
        admin_logout();
    }
    redirect(base_url('admin/index.php'));
}

admin_logout();
redirect(base_url('admin/index.php'));
