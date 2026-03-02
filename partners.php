<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.partners_title');
$pageDescription = t('partners.subtitle');

$sponsorLevels = [
    'bronze' => t('partners.level_bronze'),
    'silver' => t('partners.level_silver'),
    'gold' => t('partners.level_gold'),
    'strategic' => t('partners.level_strategic'),
];

if (is_post()) {
    verify_csrf_or_fail();
    remember_old_input($_POST);

    if (!honeypot_passed()) {
        set_flash('error', t('validation.honeypot'));
        redirect('partners.php');
    }

    $organizationName = post_string('organization_name');
    $contactPerson = post_string('contact_person');
    $email = strtolower(post_string('email'));
    $phone = normalize_phone(post_string('phone'));
    $website = post_string('website');
    $sponsorshipLevel = post_string('sponsorship_level');
    $message = post_string('message');
    $gdprConsent = isset($_POST['gdpr_consent']) ? 1 : 0;

    $errors = [];
    if ($organizationName === '' || $contactPerson === '' || $email === '' || $sponsorshipLevel === '') {
        $errors[] = t('validation.required');
    }
    if ($email !== '' && !is_valid_email($email)) {
        $errors[] = t('validation.email');
    }
    if (!array_key_exists($sponsorshipLevel, $sponsorLevels)) {
        $errors[] = t('validation.required');
    }
    if ($gdprConsent !== 1) {
        $errors[] = t('validation.gdpr');
    }

    if (!empty($errors)) {
        set_flash('error', implode(' ', array_unique($errors)));
        redirect('partners.php');
    }

    $organizerEmail = get_setting($pdo, 'organizer_email', 'organisation@guineedortmund2026.org');
    $subject = t('emails.sponsor_subject');
    $body = '<p>Organisation: ' . e($organizationName) . '</p>'
        . '<p>Contact: ' . e($contactPerson) . '</p>'
        . '<p>Email: ' . e($email) . '</p>'
        . '<p>Level: ' . e($sponsorshipLevel) . '</p>'
        . '<p>Message: ' . nl2br(e($message)) . '</p>';
    send_email($organizerEmail, 'Organisation', $subject, $body, strip_tags($body));

    clear_old_input();
    set_flash('success', t('partners.success'));
    redirect('partners.php');
}

$partnerItems = fetch_active_partners($pdo);

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1><?= e(t('partners.title')) ?></h1>
        <p class="lead"><?= e(t('partners.subtitle')) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="grid-3">
            <?php foreach ($partnerItems as $partner): ?>
                <article class="card">
                    <a href="<?= e((string) ($partner['website_url'] ?? '#')) ?>" target="_blank" rel="noopener">
                        <div class="partner-logo"><?= e((string) ($partner['name'] ?? 'Partner')) ?></div>
                    </a>
                    <p class="hint"><?= e((string) ($partner['partner_type'] ?? 'partner')) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="form-card">
            <h2><?= e(t('partners.become_sponsor')) ?></h2>
            <form method="post" action="<?= e(base_url('partners.php')) ?>" data-validate novalidate>
                <?= csrf_field() ?>
                <?= honeypot_field_html() ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="organization_name"><?= e(t('partners.organization_name')) ?> *</label>
                        <input id="organization_name" type="text" name="organization_name" value="<?= e(old('organization_name')) ?>" data-required data-label="<?= e(t('partners.organization_name')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="contact_person"><?= e(t('partners.contact_person')) ?> *</label>
                        <input id="contact_person" type="text" name="contact_person" value="<?= e(old('contact_person')) ?>" data-required data-label="<?= e(t('partners.contact_person')) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email"><?= e(t('partners.email')) ?> *</label>
                        <input id="email" type="email" name="email" value="<?= e(old('email')) ?>" data-required data-email data-label="<?= e(t('partners.email')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="phone"><?= e(t('partners.phone')) ?></label>
                        <input id="phone" type="text" name="phone" value="<?= e(old('phone')) ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="website_input"><?= e(t('partners.website')) ?></label>
                        <input id="website_input" type="url" name="website" value="<?= e(old('website')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="sponsorship_level"><?= e(t('partners.sponsorship_level')) ?> *</label>
                        <select id="sponsorship_level" name="sponsorship_level" data-required data-label="<?= e(t('partners.sponsorship_level')) ?>">
                            <option value="">--</option>
                            <?php foreach ($sponsorLevels as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= old('sponsorship_level') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message"><?= e(t('partners.message')) ?></label>
                    <textarea id="message" name="message"><?= e(old('message')) ?></textarea>
                </div>

                <div class="form-group checkbox">
                    <input id="gdpr_consent" type="checkbox" name="gdpr_consent" value="1" <?= old('gdpr_consent') === '1' ? 'checked' : '' ?> data-gdpr data-label="RGPD">
                    <label for="gdpr_consent"><?= e(t('partners.gdpr_label')) ?></label>
                </div>

                <button type="submit" class="btn btn-primary"><?= e(t('partners.submit')) ?></button>
            </form>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
