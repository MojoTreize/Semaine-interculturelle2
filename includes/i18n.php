<?php

declare(strict_types=1);

$currentLang = 'fr';
$translations = [];

if (!function_exists('init_language')) {
    function init_language(array $config): void
    {
        global $currentLang, $translations;

        $supported = $config['app']['supported_langs'] ?? ['fr', 'de'];
        $default = $config['app']['default_lang'] ?? 'fr';

        $candidate = isset($_GET['lang']) ? strtolower(trim((string) $_GET['lang'])) : '';
        if ($candidate !== '' && in_array($candidate, $supported, true)) {
            $_SESSION['lang'] = $candidate;
        }

        $sessionLang = isset($_SESSION['lang']) ? strtolower((string) $_SESSION['lang']) : '';
        if ($sessionLang !== '' && in_array($sessionLang, $supported, true)) {
            $currentLang = $sessionLang;
        } else {
            $currentLang = $default;
            $_SESSION['lang'] = $currentLang;
        }

        $langFile = ROOT_PATH . '/lang/' . $currentLang . '.php';
        if (!is_file($langFile)) {
            $langFile = ROOT_PATH . '/lang/' . $default . '.php';
            $currentLang = $default;
            $_SESSION['lang'] = $currentLang;
        }

        $loaded = require $langFile;
        $translations = is_array($loaded) ? $loaded : [];
    }
}

if (!function_exists('current_lang')) {
    function current_lang(): string
    {
        global $currentLang;
        return $currentLang;
    }
}

if (!function_exists('t')) {
    function t(string $key, array $replace = []): string
    {
        global $translations;

        $line = array_get($translations, $key, $key);
        $line = (string) $line;

        foreach ($replace as $placeholder => $value) {
            $line = str_replace(':' . $placeholder, (string) $value, $line);
        }

        return $line;
    }
}

if (!function_exists('lang_url')) {
    function lang_url(string $lang): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $path = strtok($uri, '?') ?: '/';
        $query = [];

        parse_str(parse_url($uri, PHP_URL_QUERY) ?? '', $query);
        $query['lang'] = $lang;

        $queryString = http_build_query($query);
        return $path . ($queryString !== '' ? '?' . $queryString : '');
    }
}
