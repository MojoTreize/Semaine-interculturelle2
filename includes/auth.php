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
        $normalizedEmail = strtolower(trim($email));
        $canUseConfigFallback = !($pdo instanceof PDO);

        if ($normalizedEmail === '' || $password === '') {
            return false;
        }

        if ($pdo instanceof PDO) {
            try {
                $stmt = $pdo->prepare(
                    'SELECT id, full_name, email, password_hash, role, is_active
                     FROM admins
                     WHERE email = :email
                     LIMIT 1'
                );
                $stmt->execute(['email' => $normalizedEmail]);
                $admin = $stmt->fetch();

                $isValid = is_array($admin)
                    && (int) ($admin['is_active'] ?? 0) === 1
                    && password_verify($password, (string) ($admin['password_hash'] ?? ''));

                if ($isValid) {
                    session_regenerate_id(true);
                    $_SESSION['admin'] = [
                        'id' => (int) ($admin['id'] ?? 0),
                        'full_name' => (string) ($admin['full_name'] ?? 'Admin'),
                        'email' => (string) ($admin['email'] ?? $normalizedEmail),
                        'role' => (string) ($admin['role'] ?? 'editor'),
                    ];

                    $nowExpression = db_now_expression($pdo);
                    $pdo->exec('UPDATE admins SET last_login_at = ' . $nowExpression . ' WHERE id = ' . (int) $_SESSION['admin']['id']);

                    return true;
                }

                return false;
            } catch (Throwable) {
                // Continue with fallback config-based auth.
                $canUseConfigFallback = true;
            }
        }

        if (!$canUseConfigFallback) {
            return false;
        }

        $configuredEmail = strtolower((string) app_config('admin.email', 'admin@guineedortmund2026.org'));
        $configuredPassword = (string) app_config('admin.password', 'Admin@2026');
        if ($normalizedEmail !== $configuredEmail || $password !== $configuredPassword) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id' => 1,
            'full_name' => (string) app_config('admin.full_name', 'Admin Dortmund 2026'),
            'email' => $normalizedEmail,
            'role' => 'super_admin',
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
