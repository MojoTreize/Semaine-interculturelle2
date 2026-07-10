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

if (!function_exists('admin_login_throttled')) {
    // Lightweight per-session lockout: not a substitute for IP-based/persistent
    // throttling, but it stops naive automated retries against a single session.
    function admin_login_throttled(): bool
    {
        $state = $_SESSION['admin_login_attempts'] ?? null;
        if (!is_array($state)) {
            return false;
        }

        $count = (int) ($state['count'] ?? 0);
        $lockedUntil = (int) ($state['locked_until'] ?? 0);

        return $count >= 5 && time() < $lockedUntil;
    }
}

if (!function_exists('admin_login_register_failure')) {
    function admin_login_register_failure(): void
    {
        $state = $_SESSION['admin_login_attempts'] ?? ['count' => 0, 'locked_until' => 0];
        $state['count'] = (int) ($state['count'] ?? 0) + 1;
        if ($state['count'] >= 5) {
            $state['locked_until'] = time() + 60;
        }
        $_SESSION['admin_login_attempts'] = $state;
    }
}

if (!function_exists('admin_login_reset_throttle')) {
    function admin_login_reset_throttle(): void
    {
        unset($_SESSION['admin_login_attempts']);
    }
}

if (!function_exists('admin_attempt_login')) {
    function admin_attempt_login(mixed $pdo, string $email, string $password): bool
    {
        if (admin_login_throttled()) {
            return false;
        }

        if (!is_object($pdo) || !method_exists($pdo, 'prepare')) {
            admin_login_register_failure();
            return false;
        }

        try {
            $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role
                                   FROM admins WHERE email = :email AND is_active = 1 LIMIT 1');
            $stmt->execute(['email' => strtolower(trim($email))]);
            $admin = $stmt->fetch();
        } catch (Throwable) {
            admin_login_register_failure();
            return false;
        }

        if (!is_array($admin) || !password_verify($password, (string) $admin['password_hash'])) {
            admin_login_register_failure();
            return false;
        }

        admin_login_reset_throttle();
        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id'        => (int) $admin['id'],
            'full_name' => (string) $admin['full_name'],
            'email'     => (string) $admin['email'],
            'role'      => (string) $admin['role'],
        ];

        try {
            $pdo->prepare('UPDATE admins SET last_login_at = ' . db_now_expression($pdo) . ' WHERE id = :id')
                ->execute(['id' => $admin['id']]);
        } catch (Throwable) {
            // Non-blocking: login already succeeded.
        }

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
