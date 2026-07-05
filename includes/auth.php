<?php

declare(strict_types=1);

if (!function_exists('admin_is_logged_in')) {
    function admin_is_logged_in(): bool
    {
        return isset($_SESSION['admin']) && is_array($_SESSION['admin']);
    }
}

if (!function_exists('admin_user')) {
    function admin_user(): ?array
    {
        return admin_is_logged_in() ? $_SESSION['admin'] : null;
    }
}

if (!function_exists('admin_attempt_login')) {
    function admin_attempt_login(mixed $pdo, string $email, string $password): bool
    {
        $configuredEmail    = strtolower((string) app_config('admin.email',     'admin@ugfa.de'));
        $configuredPassword = (string) app_config('admin.password',  '***REMOVED***');
        $configuredName     = (string) app_config('admin.full_name', 'Administrateur UGFA');

        if (strtolower($email) !== $configuredEmail || $password !== $configuredPassword) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id'        => 1,
            'full_name' => $configuredName,
            'email'     => $configuredEmail,
            'role'      => 'super_admin',
        ];

        return true;
    }
}

if (!function_exists('admin_require_login')) {
    function admin_require_login(): void
    {
        if (!admin_is_logged_in()) {
            set_flash('error', 'Veuillez vous connecter.');
            redirect('admin/login.php');
        }
    }
}

if (!function_exists('admin_logout')) {
    function admin_logout(): void
    {
        unset($_SESSION['admin']);
        session_regenerate_id(true);
    }
}
