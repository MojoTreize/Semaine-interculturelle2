<?php

declare(strict_types=1);

if (!function_exists('csrf_token_name')) {
    function csrf_token_name(): string
    {
        return (string) app_config('security.csrf_token_name', 'csrf_token');
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        $tokenName = csrf_token_name();

        if (empty($_SESSION[$tokenName])) {
            $_SESSION[$tokenName] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION[$tokenName];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="' . e(csrf_token_name()) . '" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('csrf_is_valid')) {
    function csrf_is_valid(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        $sessionToken = (string) ($_SESSION[csrf_token_name()] ?? '');

        if ($sessionToken === '') {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }
}

if (!function_exists('verify_csrf_or_fail')) {
    function verify_csrf_or_fail(): void
    {
        $tokenName = csrf_token_name();
        $token = isset($_POST[$tokenName]) ? (string) $_POST[$tokenName] : null;

        if (!csrf_is_valid($token)) {
            http_response_code(419);
            exit('Invalid CSRF token.');
        }
    }
}
