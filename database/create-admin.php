<?php
/**
 * Create (or reset) an admin account.
 *
 * Usage:
 *   php database/create-admin.php <username> [password]
 *   php database/create-admin.php            # prompts for both
 *
 * No default credentials are ever shipped.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("This script can only be run from the command line.\n");
}

require __DIR__ . '/../includes/helpers.php';
app_boot();

function prompt(string $label): string
{
    fwrite(STDOUT, $label);
    $line = fgets(STDIN);
    return $line === false ? '' : trim($line);
}

$username = $argv[1] ?? prompt('Username: ');
if ($username === '') {
    fwrite(STDERR, "Username is required.\n");
    exit(1);
}

$password = $argv[2] ?? '';
if ($password === '') {
    $password = prompt('Password: ');
}
if (strlen($password) < 8) {
    fwrite(STDERR, "Password must be at least 8 characters.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = db()->prepare('SELECT id FROM admins WHERE username = ?');
    $stmt->execute([$username]);
    $existing = $stmt->fetch();

    if ($existing) {
        db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')
            ->execute([$hash, $existing['id']]);
        fwrite(STDOUT, "Updated password for admin \"$username\".\n");
    } else {
        db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)')
            ->execute([$username, $hash]);
        fwrite(STDOUT, "Created admin \"$username\".\n");
    }
} catch (Throwable $ex) {
    fwrite(STDERR, 'Failed: ' . $ex->getMessage() . "\n");
    exit(1);
}
