<?php

declare(strict_types=1);

if (!function_exists('database_driver')) {
    function database_driver(array $dbConfig): string
    {
        $driver = strtolower((string) ($dbConfig['driver'] ?? 'mysql'));
        return in_array($driver, ['mysql', 'sqlite'], true) ? $driver : 'mysql';
    }
}

if (!function_exists('sqlite_database_path')) {
    function sqlite_database_path(array $dbConfig): string
    {
        $path = trim((string) ($dbConfig['path'] ?? ''));
        if ($path === '') {
            $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);
            return $root . '/database/dev.sqlite';
        }

        if ($path === ':memory:') {
            return $path;
        }

        if (preg_match('#^(?:[A-Za-z]:[\\\\/]|/)#', $path) === 1) {
            return $path;
        }

        $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);
        return $root . '/' . ltrim(str_replace('\\', '/', $path), '/');
    }
}

if (!function_exists('ensure_sqlite_schema')) {
    function ensure_sqlite_schema(PDO $pdo): void
    {
        $check = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'site_settings' LIMIT 1");
        if ($check !== false && $check->fetchColumn() !== false) {
            return;
        }

        $root = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__);
        $schemaPath = $root . '/database/schema.sqlite.sql';
        if (!is_file($schemaPath)) {
            throw new RuntimeException('SQLite schema file not found: ' . $schemaPath);
        }

        $schemaSql = file_get_contents($schemaPath);
        if ($schemaSql === false || trim($schemaSql) === '') {
            throw new RuntimeException('SQLite schema is empty: ' . $schemaPath);
        }

        $pdo->exec($schemaSql);
    }
}

if (!function_exists('pdo_connection')) {
    function pdo_connection(array $dbConfig): PDO
    {
        static $pdo = null;

        if ($pdo instanceof PDO) {
            return $pdo;
        }

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $driver = database_driver($dbConfig);

        if ($driver === 'sqlite') {
            if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
                throw new RuntimeException('PDO SQLite driver is not enabled. Enable pdo_sqlite and sqlite3 extensions.');
            }

            $path = sqlite_database_path($dbConfig);
            if ($path !== ':memory:') {
                $directory = dirname($path);
                if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
                    throw new RuntimeException('Unable to create SQLite directory: ' . $directory);
                }
            }

            $pdo = new PDO('sqlite:' . $path, null, null, $options);
            $pdo->exec('PRAGMA foreign_keys = ON');
            ensure_sqlite_schema($pdo);
            return $pdo;
        }

        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? '3306';
        $name = $dbConfig['name'] ?? '';
        $charset = $dbConfig['charset'] ?? 'utf8mb4';

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s', $host, $port, $name, $charset);
        $pdo = new PDO($dsn, (string) ($dbConfig['user'] ?? ''), (string) ($dbConfig['pass'] ?? ''), $options);

        return $pdo;
    }
}
