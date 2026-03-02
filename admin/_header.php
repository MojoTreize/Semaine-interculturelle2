<?php
declare(strict_types=1);

$adminTitle = $adminTitle ?? t('admin.title');
$activeAdmin = $activeAdmin ?? 'dashboard';
$admin = admin_user();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle) ?></title>
    <link rel="stylesheet" href="<?= e(base_url('assets/css/admin.css')) ?>">
</head>
<body>
<div class="admin-wrap">
    <aside class="admin-sidebar">
        <h1><?= e(t('admin.title')) ?></h1>
        <div class="admin-menu">
            <a class="<?= $activeAdmin === 'dashboard' ? 'active' : '' ?>" href="<?= e(admin_url('index.php')) ?>"><?= e(t('admin.dashboard')) ?></a>
            <a class="<?= $activeAdmin === 'registrations' ? 'active' : '' ?>" href="<?= e(admin_url('registrations.php')) ?>"><?= e(t('admin.menu_registrations')) ?></a>
            <a class="<?= $activeAdmin === 'donations' ? 'active' : '' ?>" href="<?= e(admin_url('donations.php')) ?>"><?= e(t('admin.menu_donations')) ?></a>
            <a class="<?= $activeAdmin === 'sponsors' ? 'active' : '' ?>" href="<?= e(admin_url('sponsors.php')) ?>"><?= e(t('admin.menu_sponsors')) ?></a>
            <a class="<?= $activeAdmin === 'contacts' ? 'active' : '' ?>" href="<?= e(admin_url('contacts.php')) ?>"><?= e(t('admin.menu_contacts')) ?></a>
            <a class="<?= $activeAdmin === 'partners' ? 'active' : '' ?>" href="<?= e(admin_url('partners.php')) ?>"><?= e(t('admin.menu_partners')) ?></a>
            <a class="<?= $activeAdmin === 'program' ? 'active' : '' ?>" href="<?= e(admin_url('program.php')) ?>"><?= e(t('admin.menu_program')) ?></a>
            <a class="<?= $activeAdmin === 'settings' ? 'active' : '' ?>" href="<?= e(admin_url('settings.php')) ?>"><?= e(t('admin.menu_settings')) ?></a>
            <a href="<?= e(admin_url('logout.php')) ?>"><?= e(t('admin.logout')) ?></a>
        </div>
    </aside>

    <main class="admin-main">
        <div class="admin-top">
            <h2><?= e($adminTitle) ?></h2>
            <div><?= $admin ? e((string) $admin['full_name']) : '' ?></div>
        </div>
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>
