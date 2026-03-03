<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.registration_title');
$pageDescription = t('registration.subtitle');

$participationOptions = [
    'participant' => t('registration.participant'),
    'partner' => t('registration.partner'),
    'speaker' => t('registration.speaker'),
    'sponsor' => t('registration.sponsor'),
];

if (is_post()) {
    verify_csrf_or_fail();
    remember_old_input($_POST);

    if (!honeypot_passed()) {
        set_flash('error', t('validation.honeypot'));
        redirect('registration.php');
    }

    $firstName = post_string('first_name');
    $lastName = post_string('last_name');
    $country = post_string('country');
    $email = strtolower(post_string('email'));
    $phone = normalize_phone(post_string('phone'));
    $organization = post_string('organization');
    $participationType = post_string('participation_type');
    $gdprConsent = isset($_POST['gdpr_consent']) ? 1 : 0;

    $errors = [];

    if ($firstName === '' || $lastName === '' || $country === '' || $email === '' || $phone === '' || $participationType === '') {
        $errors[] = t('validation.required');
    }

    if ($email !== '' && !is_valid_email($email)) {
        $errors[] = t('validation.email');
    }

    if (!array_key_exists($participationType, $participationOptions)) {
        $errors[] = t('validation.required');
    }

    if ($gdprConsent !== 1) {
        $errors[] = t('validation.gdpr');
    }

    if (!empty($errors)) {
        set_flash('error', implode(' ', array_unique($errors)));
        redirect('registration.php');
    }

    try {
        if (!($pdo instanceof PDO)) {
            throw new RuntimeException('Database connection unavailable.');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO registrations
                (first_name, last_name, country, email, phone, organization, participation_type, gdpr_consent, language, ip_address, user_agent)
             VALUES
                (:first_name, :last_name, :country, :email, :phone, :organization, :participation_type, :gdpr_consent, :language, :ip_address, :user_agent)'
        );
        $stmt->execute([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'country' => $country,
            'email' => $email,
            'phone' => $phone,
            'organization' => $organization !== '' ? $organization : null,
            'participation_type' => $participationType,
            'gdpr_consent' => $gdprConsent,
            'language' => current_lang(),
            'ip_address' => request_ip(),
            'user_agent' => substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        ]);
    } catch (Throwable) {
        set_flash('error', t('validation.server_error'));
        redirect('registration.php');
    }

    $subject = t('emails.registration_subject');
    $body = t('emails.registration_body', ['name' => $firstName . ' ' . $lastName]);
    send_email($email, trim($firstName . ' ' . $lastName), $subject, $body, strip_tags($body));

    clear_old_input();
    set_flash('success', t('registration.success'));
    redirect('registration.php');
}

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1><?= e(t('registration.title')) ?></h1>
        <p class="lead"><?= e(t('registration.subtitle')) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="form-card">
            <form method="post" action="<?= e(base_url('registration.php')) ?>" data-validate novalidate>
                <?= csrf_field() ?>
                <?= honeypot_field_html() ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name"><?= e(t('registration.first_name')) ?> *</label>
                        <input id="first_name" type="text" name="first_name" value="<?= e(old('first_name')) ?>" data-required data-label="<?= e(t('registration.first_name')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="last_name"><?= e(t('registration.last_name')) ?> *</label>
                        <input id="last_name" type="text" name="last_name" value="<?= e(old('last_name')) ?>" data-required data-label="<?= e(t('registration.last_name')) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="country"><?= e(t('registration.country')) ?> *</label>
                        <input id="country" type="text" name="country" value="<?= e(old('country')) ?>" data-required data-label="<?= e(t('registration.country')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="email"><?= e(t('registration.email')) ?> *</label>
                        <input id="email" type="email" name="email" value="<?= e(old('email')) ?>" data-required data-email data-label="<?= e(t('registration.email')) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="phone"><?= e(t('registration.phone')) ?> *</label>
                        <input id="phone" type="text" name="phone" value="<?= e(old('phone')) ?>" data-required data-label="<?= e(t('registration.phone')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="organization"><?= e(t('registration.organization')) ?></label>
                        <input id="organization" type="text" name="organization" value="<?= e(old('organization')) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="participation_type"><?= e(t('registration.participation_type')) ?> *</label>
                    <select id="participation_type" name="participation_type" data-required data-label="<?= e(t('registration.participation_type')) ?>">
                        <option value="">--</option>
                        <?php foreach ($participationOptions as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= old('participation_type') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group checkbox">
                    <input id="gdpr_consent" type="checkbox" name="gdpr_consent" value="1" <?= old('gdpr_consent') === '1' ? 'checked' : '' ?> data-gdpr data-label="RGPD">
                    <label for="gdpr_consent"><?= e(t('registration.gdpr_label')) ?></label>
                </div>

                <button type="submit" class="btn btn-primary"><?= e(t('registration.submit')) ?></button>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
