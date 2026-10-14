<?php
/**
 * Dev router for PHP's built-in server. Emulates the Apache .htaccess rules.
 *
 * Usage:  php -S 127.0.0.1:8000 router.php
 * (Uses the project root as the document root.)
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');
$root = __DIR__;

// Mirror the "block private folders" rule.
if (preg_match('#^/(config|database|includes|logs|docker)(/|$)#', $uri)) {
    http_response_code(403);
    exit('Forbidden');
}

function serve_static(string $file): bool
{
    if (!is_file($file) || str_ends_with($file, '.php')) {
        return false;
    }
    $types = [
        'css' => 'text/css; charset=utf-8',
        'js' => 'application/javascript; charset=utf-8',
        'svg' => 'image/svg+xml',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'ico' => 'image/x-icon',
        'xml' => 'application/xml; charset=utf-8',
        'txt' => 'text/plain; charset=utf-8',
        'woff2' => 'font/woff2',
    ];
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    readfile($file);
    return true;
}

// Admin is served from the project root.
if (preg_match('#^/admin#', $uri)) {
    $file = $root . $uri;
    if (is_file($file)) {
        if (str_ends_with($file, '.php')) {
            chdir(dirname($file));
            require $file;
            return true;
        }
        serve_static($file);
        return true;
    }
    http_response_code(404);
    chdir($root . '/public');
    require $root . '/public/404.php';
    return true;
}

// Everything else maps into /public.
$public = $root . '/public';
$path = $uri === '/' ? '/index.php' : $uri;

$file = $public . $path;
if (is_file($file)) {
    if (str_ends_with($file, '.php')) {
        chdir(dirname($file));
        require $file;
        return true;
    }
    serve_static($file);
    return true;
}

// Extensionless clean URL (e.g. /contact -> contact.php).
$phpFile = $public . rtrim($path, '/') . '.php';
if (is_file($phpFile)) {
    chdir(dirname($phpFile));
    require $phpFile;
    return true;
}

http_response_code(404);
chdir($public);
require $public . '/404.php';
return true;
