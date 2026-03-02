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
    function admin_attempt_login(PDO $pdo, string $email, string $password): bool
    {
        $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role FROM admins WHERE email = :email AND is_active = 1 LIMIT 1');
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch();

        if (!$admin) {
            return false;
        }

        if (!password_verify($password, (string) $admin['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id' => (int) $admin['id'],
            'full_name' => (string) $admin['full_name'],
            'email' => (string) $admin['email'],
            'role' => (string) $admin['role'],
        ];

        $update = $pdo->prepare('UPDATE admins SET last_login_at = ' . db_now_expression($pdo) . ' WHERE id = :id');
        $update->execute(['id' => (int) $admin['id']]);

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
