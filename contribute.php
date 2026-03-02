<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.contribute_title');
$pageDescription = t('contribute.subtitle');

$motiveOptions = [
    'general' => t('contribute.motive_general'),
    'logistics' => t('contribute.motive_logistics'),
    'youth' => t('contribute.motive_youth'),
    'culture' => t('contribute.motive_culture'),
    'other' => t('contribute.motive_other'),
];

$methodOptions = [
    'stripe' => t('contribute.method_stripe'),
    'paypal' => t('contribute.method_paypal'),
    'bank_transfer' => t('contribute.method_bank_transfer'),
];

if (is_post()) {
    verify_csrf_or_fail();
    remember_old_input($_POST);

    if (!honeypot_passed()) {
        set_flash('error', t('validation.honeypot'));
        redirect('contribute.php');
    }

    $donorName = post_string('donor_name');
    $donorEmail = strtolower(post_string('donor_email'));
    $amountRaw = str_replace([' ', ','], ['', '.'], post_string('amount'));
    $amount = is_numeric($amountRaw) ? (float) $amountRaw : 0;
    $motive = post_string('motive');
    $customMotive = post_string('custom_motive');
    $message = post_string('message');
    $paymentMethod = post_string('payment_method');
    $gdprConsent = isset($_POST['gdpr_consent']) ? 1 : 0;

    $errors = [];
    if ($amount <= 0) {
        $errors[] = t('validation.amount');
    }
    if ($donorEmail !== '' && !is_valid_email($donorEmail)) {
        $errors[] = t('validation.email');
    }
    if (!array_key_exists($motive, $motiveOptions)) {
        $errors[] = t('validation.required');
    }
    if ($motive === 'other' && $customMotive === '') {
        $errors[] = t('validation.required');
    }
    if (!array_key_exists($paymentMethod, $methodOptions)) {
        $errors[] = t('validation.required');
    }
    if ($gdprConsent !== 1) {
        $errors[] = t('validation.gdpr');
    }

    if (!empty($errors)) {
        set_flash('error', implode(' ', array_unique($errors)));
        redirect('contribute.php');
    }

    if ($paymentMethod === 'stripe') {
        set_flash('error', t('contribute.payment_error_stripe'));
        redirect('contribute.php');
    }

    if ($paymentMethod === 'paypal') {
        set_flash('error', t('contribute.payment_error_paypal'));
        redirect('contribute.php');
    }

    if ($donorEmail !== '') {
        $subject = t('emails.donation_subject');
        $body = '<p>' . e(t('contribute.thanks_pending')) . '</p>';
        send_email($donorEmail, $donorName !== '' ? $donorName : $donorEmail, $subject, $body, strip_tags($body));
    }

    clear_old_input();
    set_flash('success', t('contribute.thanks_pending'));
    redirect('contribute.php');
}

$totals = collection_totals($pdo);
$goalAmount = (float) get_setting($pdo, 'collection_goal', '50000');

$bankHolder = get_setting($pdo, 'bank_holder', 'Association Guinee Forestiere Allemagne e.V.');
$bankIban = get_setting($pdo, 'bank_iban', 'DE00 0000 0000 0000 0000 00');
$bankBic = get_setting($pdo, 'bank_bic', 'GENODE00XXX');
$bankName = get_setting($pdo, 'bank_name', 'Banque Exemple Dortmund');

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1><?= e(t('contribute.title')) ?></h1>
        <p class="lead"><?= e(t('contribute.subtitle')) ?></p>
        <div class="stats-strip">
            <article class="stat-card">
                <span><?= e(t('contribute.total_collected')) ?></span>
                <strong><?= e(format_amount($totals['amount'])) ?></strong>
            </article>
            <article class="stat-card">
                <span><?= e(t('contribute.donors_count')) ?></span>
                <strong><?= e((string) $totals['count']) ?></strong>
            </article>
            <article class="stat-card">
                <span><?= e(t('contribute.goal')) ?></span>
                <strong><?= e(format_amount($goalAmount)) ?></strong>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container grid-2">
        <article class="form-card">
            <form method="post" action="<?= e(base_url('contribute.php')) ?>" data-validate novalidate>
                <?= csrf_field() ?>
                <?= honeypot_field_html() ?>

                <div class="form-group">
                    <label for="donor_name"><?= e(t('contribute.donor_name')) ?></label>
                    <input id="donor_name" type="text" name="donor_name" value="<?= e(old('donor_name')) ?>">
                </div>

                <div class="form-group">
                    <label for="donor_email"><?= e(t('contribute.donor_email')) ?></label>
                    <input id="donor_email" type="email" name="donor_email" value="<?= e(old('donor_email')) ?>" data-email data-label="<?= e(t('contribute.donor_email')) ?>">
                </div>

                <div class="form-group">
                    <label for="amount"><?= e(t('contribute.amount')) ?> *</label>
                    <input id="amount" type="number" step="0.01" min="1" name="amount" value="<?= e(old('amount')) ?>" data-required data-label="<?= e(t('contribute.amount')) ?>">
                </div>

                <div class="form-group">
                    <label for="motive"><?= e(t('contribute.motive')) ?> *</label>
                    <select id="motive" name="motive" data-required data-label="<?= e(t('contribute.motive')) ?>">
                        <option value="">--</option>
                        <?php foreach ($motiveOptions as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= old('motive') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="custom_motive"><?= e(t('contribute.custom_motive')) ?></label>
                    <input id="custom_motive" type="text" name="custom_motive" value="<?= e(old('custom_motive')) ?>">
                </div>

                <div class="form-group">
                    <label for="message"><?= e(t('contribute.message')) ?></label>
                    <textarea id="message" name="message"><?= e(old('message')) ?></textarea>
                </div>

                <div class="form-group">
                    <label for="payment_method"><?= e(t('contribute.payment_method')) ?> *</label>
                    <select id="payment_method" name="payment_method" data-required data-label="<?= e(t('contribute.payment_method')) ?>">
                        <option value="">--</option>
                        <?php foreach ($methodOptions as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= old('payment_method') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group checkbox">
                    <input id="gdpr_consent" type="checkbox" name="gdpr_consent" value="1" <?= old('gdpr_consent') === '1' ? 'checked' : '' ?> data-gdpr data-label="RGPD">
                    <label for="gdpr_consent"><?= e(t('registration.gdpr_label')) ?></label>
                </div>

                <button type="submit" class="btn btn-primary"><?= e(t('contribute.submit')) ?></button>
            </form>
        </article>

        <article class="card">
            <h2><?= e(t('contribute.bank_title')) ?></h2>
            <p><strong><?= e(t('contribute.bank_holder')) ?>:</strong> <?= e($bankHolder) ?></p>
            <p><strong><?= e(t('contribute.bank_iban')) ?>:</strong> <?= e($bankIban) ?></p>
            <p><strong><?= e(t('contribute.bank_bic')) ?>:</strong> <?= e($bankBic) ?></p>
            <p><strong><?= e(t('contribute.bank_name')) ?>:</strong> <?= e($bankName) ?></p>
            <p class="hint"><?= e(t('contribute.security_note')) ?></p>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
