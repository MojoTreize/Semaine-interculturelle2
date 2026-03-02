<?php

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

$configPath = ROOT_PATH . '/config/config.php';
if (!is_file($configPath)) {
    $configPath = ROOT_PATH . '/config/config.example.php';
}

$config = require $configPath;

date_default_timezone_set((string) ($config['app']['timezone'] ?? 'Europe/Berlin'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    $cookieSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_name((string) ($config['security']['session_name'] ?? 'gd2026_session'));
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $cookieSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=()');
}

require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/data_program.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/i18n.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/mailer.php';
require_once ROOT_PATH . '/includes/payment.php';

$pdo = pdo_connection((array) ($config['db'] ?? []));

init_language($config);
