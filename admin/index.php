<?php
/** Admin login. */
require_once __DIR__ . '/includes/auth.php';

if (admin_user() !== null) {
    redirect(base_url('admin/dashboard.php'));
}

$error = '';
$username = '';

if (is_post()) {
    if (!csrf_verify($_POST['csrf_token'] ?? null)) {
        $error = 'Your session expired. Please try again.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Enter your username and password.';
        } else {
            [$ok, $message] = admin_attempt_login($username, $password);
            if ($ok) {
                redirect(base_url('admin/dashboard.php'));
            }
            $error = $message;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in — Sobur Admin</title>
    <link rel="icon" href="<?= e(base_url('favicon.svg')) ?>" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@600;700&display=swap">
    <link rel="stylesheet" href="<?= e(base_url('admin/assets/css/admin.css')) ?>">
</head>
<body class="login-body">
    <main class="login-card">
        <h1>Sobur Admin</h1>
        <p class="login-sub">Sign in to manage your portfolio.</p>

        <?php if ($error !== ''): ?>
            <div class="notice notice--err" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= e(base_url('admin/index.php')) ?>" class="admin-form">
            <?= csrf_field() ?>
            <div class="field">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" autocomplete="username" required autofocus value="<?= e($username) ?>">
            </div>
            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required>
            </div>
            <button class="btn btn--block" type="submit">Sign in</button>
        </form>
    </main>
</body>
</html>
