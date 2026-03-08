<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$settingKeys = [
    'site_domain',
    'contact_email',
    'organizer_email',
    'bank_holder',
    'bank_iban',
    'bank_bic',
    'bank_name',
    'stripe_public_key',
    'stripe_secret_key',
    'stripe_webhook_secret',
    'paypal_business_email',
    'paypal_mode',
    'collection_goal',
];

if (is_post()) {
    verify_csrf_or_fail();
    foreach ($settingKeys as $key) {
        $value = post_string($key);
        set_setting($pdo, $key, $value);
    }
    set_flash('success', 'Parametres enregistres.');
    redirect('admin/settings.php');
}

$settings = [];
foreach ($settingKeys as $key) {
    $settings[$key] = get_setting($pdo, $key, '');
}

$adminTitle = t('admin.menu_settings');
$activeAdmin = 'settings';
require __DIR__ . '/_header.php';
?>

<section class="card">
    <form method="post">
        <?= csrf_field() ?>
        <div class="row">
            <?php foreach ($settings as $key => $value): ?>
                <div>
                    <label for="<?= e($key) ?>"><?= e($key) ?></label>
                    <input id="<?= e($key) ?>" type="text" name="<?= e($key) ?>" value="<?= e($value) ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <p><button type="submit"><?= e(t('buttons.save')) ?></button></p>
    </form>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
