<?php
/**
 * Configuration example for the Sobur portfolio.
 *
 * Copy this file to config/config.php and fill in your own values.
 * config/config.php is gitignored and must never be committed.
 */

return [
    // ---- Database (MySQL / MariaDB) ----------------------------------
    // Environment variables (DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS)
    // take priority, which is handy for Docker. Otherwise the literals are used.
    'db' => [
        'host'    => getenv('DB_HOST') ?: '127.0.0.1',
        'port'    => (int) (getenv('DB_PORT') ?: 3306),
        'name'    => getenv('DB_NAME') ?: 'sobur_portfolio',
        'user'    => getenv('DB_USER') ?: 'root',   // TODO: your DB username
        'pass'    => getenv('DB_PASS') ?: '',       // TODO: your DB password
        'charset' => 'utf8mb4',
    ],

    // ---- Site --------------------------------------------------------
    'site' => [
        'url'      => getenv('SITE_URL') ?: 'http://localhost:8000',  // no trailing slash
        'name'     => 'Sobur',
        'email'    => 'soburismail490@gmail.com',              // where contact messages are sent
        'whatsapp' => 'https://wa.me/2348144104649',
    ],

    // ---- Mail --------------------------------------------------------
    // method: "mail" (PHP mail()) or "smtp".
    // TODO: to send real mail reliably, set method to "smtp" and fill these in.
    'mail' => [
        'method'    => 'mail',
        'host'      => '',            // TODO: smtp.example.com
        'port'      => 587,
        'user'      => '',            // TODO
        'pass'      => '',            // TODO
        'secure'    => 'tls',         // 'tls' or 'ssl'
        'from'      => 'no-reply@localhost',
        'from_name' => 'Sobur Portfolio',
    ],

    // ---- Security ----------------------------------------------------
    'security' => [
        'session_timeout'     => 1800,   // seconds of inactivity before admin logout
        'max_login_attempts'  => 5,      // failed logins before lockout
        'lockout_minutes'     => 15,     // lockout duration
        'contact_rate_limit'  => 5,      // max contact messages per IP per window
        'contact_rate_window' => 3600,   // window in seconds
    ],

    // ---- Environment -------------------------------------------------
    // "local" shows errors on screen; "production" hides them and logs to /logs.
    'env' => 'local',
];
