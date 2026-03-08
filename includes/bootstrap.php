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

require_once ROOT_PATH . '/includes/data_program.php';
require_once ROOT_PATH . '/includes/functions.php';
require_once ROOT_PATH . '/includes/csrf.php';
require_once ROOT_PATH . '/includes/i18n.php';
require_once ROOT_PATH . '/includes/auth.php';
require_once ROOT_PATH . '/includes/mailer.php';
require_once ROOT_PATH . '/includes/payment.php';

$pdo = null;
$dbConfig = app_config('db', []);

try {
    $driver = strtolower((string) ($dbConfig['driver'] ?? 'sqlite'));

    if ($driver === 'sqlite') {
        $sqlitePath = (string) ($dbConfig['path'] ?? (ROOT_PATH . '/database/dev.sqlite'));
        $sqliteDir = dirname($sqlitePath);
        if (!is_dir($sqliteDir)) {
            mkdir($sqliteDir, 0775, true);
        }

        $needsInit = !is_file($sqlitePath) || filesize($sqlitePath) === 0;

        $pdo = new PDO('sqlite:' . $sqlitePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        if ($needsInit) {
            $schemaPath = ROOT_PATH . '/database/schema.sqlite.sql';
            if (is_file($schemaPath)) {
                $schemaSql = (string) file_get_contents($schemaPath);
                if (trim($schemaSql) !== '') {
                    $pdo->exec($schemaSql);
                }
            }
        }
    } elseif ($driver === 'mysql') {
        $host = (string) ($dbConfig['host'] ?? '127.0.0.1');
        $port = (string) ($dbConfig['port'] ?? '3306');
        $name = (string) ($dbConfig['name'] ?? '');
        $user = (string) ($dbConfig['user'] ?? '');
        $pass = (string) ($dbConfig['pass'] ?? '');
        $charset = (string) ($dbConfig['charset'] ?? 'utf8mb4');

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } else {
        throw new RuntimeException('Unsupported database driver: ' . $driver);
    }
} catch (Throwable) {
    http_response_code(500);
    exit('Database connection error.');
}

init_language($config);
