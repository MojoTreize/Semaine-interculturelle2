<?php

declare(strict_types=1);

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

if (!function_exists('load_dotenv_file')) {
    /**
     * Lightweight .env loader without external dependencies.
     */
    function load_dotenv_file(string $filepath): void
    {
        if (!is_file($filepath) || !is_readable($filepath)) {
            return;
        }

        $lines = file($filepath, FILE_IGNORE_NEW_LINES);
        if (!is_array($lines)) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim((string) $line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_starts_with($line, 'export ')) {
                $line = trim(substr($line, 7));
            }

            if (!str_contains($line, '=')) {
                continue;
            }

            [$key, $rawValue] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($rawValue);

            if ($key === '' || preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key) !== 1) {
                continue;
            }

            if ($value !== '' && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
                $quote = $value[0];
                $value = substr($value, 1, -1);

                if ($quote === '"') {
                    $value = strtr($value, [
                        '\n' => "\n",
                        '\r' => "\r",
                        '\t' => "\t",
                        '\"' => '"',
                        '\\\\' => '\\',
                    ]);
                }
            } else {
                $value = preg_replace('/\s+#.*$/', '', $value) ?? $value;
                $value = trim($value);
            }

            $existing = getenv($key);
            if ($existing !== false && $existing !== '') {
                continue;
            }

            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

load_dotenv_file(ROOT_PATH . '/.env');

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

$dbConfig = is_array($config['db'] ?? null) ? $config['db'] : [];
$dbDriver = strtolower(trim((string) ($dbConfig['driver'] ?? 'mysql')));
$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    if ($dbDriver === 'sqlite') {
        $sqlitePath = (string) ($dbConfig['path'] ?? ROOT_PATH . '/database/dev.sqlite');
        if ($sqlitePath !== '') {
            if (
                !preg_match('#^(?:[A-Za-z]:[\\\\/]|/)#', $sqlitePath)
            ) {
                $sqlitePath = ROOT_PATH . '/' . ltrim($sqlitePath, '/\\');
            }

            $sqliteDir = dirname($sqlitePath);
            if (!is_dir($sqliteDir)) {
                @mkdir($sqliteDir, 0775, true);
            }

            $isNewFile = !is_file($sqlitePath) || filesize($sqlitePath) === 0;
            $pdo = new PDO('sqlite:' . $sqlitePath, null, null, $pdoOptions);
            $pdo->exec('PRAGMA foreign_keys = ON');

            $hasAdminsTable = false;
            try {
                $checkStmt = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'admins' LIMIT 1");
                $hasAdminsTable = $checkStmt !== false && $checkStmt->fetchColumn() !== false;
            } catch (Throwable) {
                $hasAdminsTable = false;
            }

            if ($isNewFile || !$hasAdminsTable) {
                $sqliteSchema = ROOT_PATH . '/database/schema.sqlite.sql';
                if (is_file($sqliteSchema)) {
                    $schemaSql = (string) file_get_contents($sqliteSchema);
                    if ($schemaSql !== '') {
                        $pdo->exec($schemaSql);
                    }
                }
            }
        }
    } else {
        $host = (string) ($dbConfig['host'] ?? '127.0.0.1');
        $port = (string) ($dbConfig['port'] ?? '3306');
        $name = (string) ($dbConfig['name'] ?? '');
        $user = (string) ($dbConfig['user'] ?? '');
        $pass = (string) ($dbConfig['pass'] ?? '');
        $charset = (string) ($dbConfig['charset'] ?? 'utf8mb4');

        if ($name !== '') {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $host,
                $port,
                $name,
                $charset
            );
            $pdo = new PDO($dsn, $user, $pass, $pdoOptions);
            $pdo->exec("SET time_zone = '+00:00'");
        }
    }
} catch (Throwable) {
    $pdo = null;
}

init_language($config);

if ($pdo instanceof PDO && PHP_SAPI !== 'cli') {
    $requestPath = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/'), '?') ?: '/';
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $isAdminRequest = str_contains($scriptName, '/admin/');

    if (!$isAdminRequest) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO page_views (page_path, page_title, referrer, language, session_id, ip_address, user_agent)
                 VALUES (:page_path, :page_title, :referrer, :language, :session_id, :ip_address, :user_agent)'
            );
            $stmt->execute([
                'page_path' => substr($requestPath, 0, 255),
                'page_title' => basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php')),
                'referrer' => substr((string) ($_SERVER['HTTP_REFERER'] ?? ''), 0, 255),
                'language' => current_lang(),
                'session_id' => substr((string) session_id(), 0, 128),
                'ip_address' => substr((string) request_ip(), 0, 45),
                'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]);
        } catch (Throwable) {
            // Analytics tracking is optional and must not break page rendering.
        }
    }
}
