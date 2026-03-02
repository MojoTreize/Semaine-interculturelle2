<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

if (admin_is_logged_in()) {
    redirect('admin/index.php');
}

if (is_post()) {
    verify_csrf_or_fail();

    $email = strtolower(post_string('email'));
    $password = post_string('password');

    if ($email === '' || $password === '') {
        set_flash('error', t('validation.required'));
        redirect('admin/login.php');
    }

    if (admin_attempt_login($pdo, $email, $password)) {
        set_flash('success', 'Connexion reussie.');
        redirect('admin/index.php');
    }

    set_flash('error', 'Identifiants invalides.');
    redirect('admin/login.php');
}

$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(t('admin.login_title')) ?></title>
    <link rel="stylesheet" href="<?= e(base_url('assets/css/admin.css')) ?>">
</head>
<body>
<main style="max-width:480px;margin:4rem auto;background:#fff;padding:1.2rem;border-radius:12px;border:1px solid #d6ddea;">
    <h1><?= e(t('admin.login_title')) ?></h1>
    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>">
            <?= e($flash['message']) ?>
        </div>
    <?php endif; ?>
    <form method="post" action="<?= e(admin_url('login.php')) ?>">
        <?= csrf_field() ?>
        <div style="margin-bottom:0.75rem;">
            <label for="email"><?= e(t('admin.email')) ?></label>
            <input id="email" type="email" name="email" required>
        </div>
        <div style="margin-bottom:0.75rem;">
            <label for="password"><?= e(t('admin.password')) ?></label>
            <input id="password" type="password" name="password" required>
        </div>
        <button type="submit"><?= e(t('buttons.login')) ?></button>
    </form>
    <p class="hint">admin@guineedortmund2026.org / Admin@2026</p>
</main>
</body>
</html>
