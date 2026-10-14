<?php
/**
 * Admin authentication & session guards.
 */
require_once dirname(__DIR__, 2) . '/includes/helpers.php';
app_boot();
secure_session_start();

function admin_user(): ?array
{
    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    $timeout = (int) config('security.session_timeout');
    if ($timeout > 0 && isset($_SESSION['last_activity'])
        && (time() - (int) $_SESSION['last_activity']) > $timeout) {
        admin_logout();
        return null;
    }

    $_SESSION['last_activity'] = time();
    return ['id' => (int) $_SESSION['admin_id'], 'username' => (string) ($_SESSION['admin_username'] ?? '')];
}

function admin_logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function require_admin(): array
{
    $user = admin_user();
    if ($user === null) {
        redirect(base_url('admin/index.php'));
    }
    return $user;
}

/**
 * @return array{0: bool, 1: string}
 */
function admin_attempt_login(string $username, string $password): array
{
    $ip = client_ip();

    if (login_is_locked($ip, $username)) {
        return [false, 'Too many failed attempts. Please wait and try again.'];
    }

    $stmt = db()->prepare('SELECT * FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password_hash'])) {
        record_login_attempt($ip, $username, true);
        clear_login_attempts($ip, $username);

        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int) $admin['id'];
        $_SESSION['admin_username'] = (string) $admin['username'];
        $_SESSION['last_activity'] = time();
        return [true, ''];
    }

    record_login_attempt($ip, $username, false);
    return [false, 'Invalid username or password.'];
}
