<?php
/**
 * Shared helpers: config, database, escaping, CSRF, sessions, rate limiting.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------
// Bootstrap (errors, timezone, logging)
// ---------------------------------------------------------------------
function app_boot(): void
{
    static $booted = false;
    if ($booted) {
        return;
    }
    $booted = true;

    date_default_timezone_set('UTC');

    $env = config('env') ?: 'production';
    if ($env === 'production') {
        ini_set('display_errors', '0');
        ini_set('display_startup_errors', '0');
        error_reporting(E_ALL);
        ini_set('log_errors', '1');
        $logDir = dirname(__DIR__) . '/logs';
        if (is_dir($logDir) && is_writable($logDir)) {
            ini_set('error_log', $logDir . '/error.log');
        }
    } else {
        ini_set('display_errors', '1');
        error_reporting(E_ALL);
    }
}

// ---------------------------------------------------------------------
// Configuration
// ---------------------------------------------------------------------
function config(?string $key = null)
{
    static $config = null;

    if ($config === null) {
        $path = dirname(__DIR__) . '/config/config.php';
        if (!is_file($path)) {
            http_response_code(500);
            exit('Configuration missing: copy config/config.example.php to config/config.php and edit it.');
        }
        $config = require $path;
    }

    if ($key === null) {
        return $config;
    }

    $value = $config;
    foreach (explode('.', $key) as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return null;
        }
        $value = $value[$segment];
    }
    return $value;
}

// ---------------------------------------------------------------------
// Database (PDO singleton)
// ---------------------------------------------------------------------
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $c = config('db');
    $dsn = sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $c['host'],
        (int) $c['port'],
        $c['name'],
        $c['charset']
    );

    try {
        $pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $ex) {
        error_log('DB connection failed: ' . $ex->getMessage());
        http_response_code(500);
        exit('Database connection failed. Check config/config.php.');
    }

    return $pdo;
}

// ---------------------------------------------------------------------
// Output escaping
// ---------------------------------------------------------------------
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string
{
    $base = rtrim(config('site.url') ?: '', '/');
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return base_url('assets/' . ltrim($path, '/'));
}

// ---------------------------------------------------------------------
// Sessions
// ---------------------------------------------------------------------
function secure_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('sobur_sess');
    session_start();
}

// ---------------------------------------------------------------------
// CSRF
// ---------------------------------------------------------------------
function csrf_token(): string
{
    secure_session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify(?string $token): bool
{
    secure_session_start();
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// ---------------------------------------------------------------------
// Requests
// ---------------------------------------------------------------------
function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45);
}

function user_agent(): string
{
    return substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function wants_json(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xhr    = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
    return str_contains($accept, 'application/json') || $xhr === 'xmlhttprequest';
}

function json_response(array $data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

// ---------------------------------------------------------------------
// Validation helpers
// ---------------------------------------------------------------------
/** Header-injection protection: reject CR/LF in values used in mail headers. */
function has_header_injection(string $value): bool
{
    return (bool) preg_match('/[\r\n]/', $value);
}

function valid_email(string $email): bool
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ---------------------------------------------------------------------
// Flash messages (session based, for no-JS fallback)
// ---------------------------------------------------------------------
function flash_set(string $key, string $message): void
{
    secure_session_start();
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    secure_session_start();
    if (empty($_SESSION['flash'][$key])) {
        return null;
    }
    $message = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $message;
}

// ---------------------------------------------------------------------
// Rate limiting
// ---------------------------------------------------------------------
function contact_rate_ok(string $ip): bool
{
    $max    = (int) config('security.contact_rate_limit');
    $window = (int) config('security.contact_rate_window');

    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM messages
         WHERE ip_address = ? AND created_at > (NOW() - INTERVAL ? SECOND)'
    );
    $stmt->bindValue(1, $ip);
    $stmt->bindValue(2, $window, PDO::PARAM_INT);
    $stmt->execute();

    return ((int) $stmt->fetchColumn()) < $max;
}

function login_is_locked(string $ip, string $username): bool
{
    $max   = (int) config('security.max_login_attempts');
    $mins  = (int) config('security.lockout_minutes');

    $stmt = db()->prepare(
        'SELECT COUNT(*) FROM login_attempts
         WHERE ip_address = ? AND username = ? AND successful = 0
           AND attempted_at > (NOW() - INTERVAL ? MINUTE)'
    );
    $stmt->bindValue(1, $ip);
    $stmt->bindValue(2, $username);
    $stmt->bindValue(3, $mins, PDO::PARAM_INT);
    $stmt->execute();

    return ((int) $stmt->fetchColumn()) >= $max;
}

function record_login_attempt(string $ip, string $username, bool $successful): void
{
    $stmt = db()->prepare(
        'INSERT INTO login_attempts (ip_address, username, successful) VALUES (?, ?, ?)'
    );
    $stmt->execute([$ip, $username, $successful ? 1 : 0]);
}

function clear_login_attempts(string $ip, string $username): void
{
    $stmt = db()->prepare(
        'DELETE FROM login_attempts WHERE ip_address = ? AND username = ?'
    );
    $stmt->execute([$ip, $username]);
}

// ---------------------------------------------------------------------
// Projects data access
// ---------------------------------------------------------------------
function get_projects(): array
{
    $stmt = db()->query(
        'SELECT * FROM projects ORDER BY sort_order ASC, id ASC'
    );
    return $stmt->fetchAll();
}

function split_tags(string $tags): array
{
    $parts = array_map('trim', explode(',', $tags));
    return array_values(array_filter($parts, static fn ($t) => $t !== ''));
}

function is_mockup_supported(string $type): bool
{
    return in_array($type, ['isobash', 'abagaf', 'prime-estates'], true);
}

function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}
