<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.contact_title');
$pageDescription = t('contact.subtitle');

if (is_post()) {
    verify_csrf_or_fail();
    remember_old_input($_POST);

    if (!honeypot_passed()) {
        set_flash('error', t('validation.honeypot'));
        redirect('contact.php');
    }

    $fullName = post_string('full_name');
    $email = strtolower(post_string('email'));
    $subject = post_string('subject');
    $message = post_string('message');
    $gdprConsent = isset($_POST['gdpr_consent']) ? 1 : 0;

    $errors = [];
    if ($fullName === '' || $email === '' || $subject === '' || $message === '') {
        $errors[] = t('validation.required');
    }
    if ($email !== '' && !is_valid_email($email)) {
        $errors[] = t('validation.email');
    }
    if ($gdprConsent !== 1) {
        $errors[] = t('validation.gdpr');
    }

    if (!empty($errors)) {
        set_flash('error', implode(' ', array_unique($errors)));
        redirect('contact.php');
    }

    $organizerEmail = get_setting($pdo, 'organizer_email', 'organisation@guineedortmund2026.org');
    $mailSubject = t('emails.contact_subject');
    $mailBody = '<p>Nom: ' . e($fullName) . '</p>'
        . '<p>Email: ' . e($email) . '</p>'
        . '<p>Objet: ' . e($subject) . '</p>'
        . '<p>Message:<br>' . nl2br(e($message)) . '</p>';
    send_email($organizerEmail, 'Organisation', $mailSubject, $mailBody, strip_tags($mailBody));

    clear_old_input();
    set_flash('success', t('contact.success'));
    redirect('contact.php');
}

$officialEmail = get_setting($pdo, 'contact_email', 'contact@guineedortmund2026.org');
$siteDomain = get_setting($pdo, 'site_domain', base_url(''));

require __DIR__ . '/includes/header.php';
?>

<section class="section contact-intro-section">
    <div class="container">
        <div class="about-section-head contact-intro-shell" data-aos="fade-up">
            <h1><?= e(t('contact.title')) ?></h1>
            <p class="lead"><?= e(t('contact.subtitle')) ?></p>
            <p><?= e(t('contact.intro_text')) ?></p>
        </div>
    </div>
</section>

<section class="section about-stats-section contact-stats-section">
    <div class="container">
        <div class="stats-strip about-stats-strip">
            <article class="stat-card about-stat-card" data-aos="zoom-in">
                <strong data-counter-end="1">0</strong>
                <span><?= e(t('contact.stat_location')) ?></span>
            </article>
            <article class="stat-card about-stat-card" data-aos="zoom-in" data-aos-delay="100">
                <strong data-counter-end="3">0</strong>
                <span><?= e(t('contact.stat_channels')) ?></span>
            </article>
            <article class="stat-card about-stat-card" data-aos="zoom-in" data-aos-delay="200">
                <strong data-counter-end="24" data-counter-suffix="h">0</strong>
                <span><?= e(t('contact.stat_response')) ?></span>
            </article>
        </div>
    </div>
</section>

<section class="section about-roadmap-section">
    <div class="container contact-layout">
        <article class="form-card contact-form-card" data-aos="fade-right">
            <div class="contact-form-head">
                <h2><?= e(t('contact.form_title')) ?></h2>
                <p class="hint"><?= e(t('contact.form_intro')) ?></p>
            </div>

            <form method="post" action="<?= e(base_url('contact.php')) ?>" data-validate novalidate>
                <?= csrf_field() ?>
                <?= honeypot_field_html() ?>

                <div class="form-group">
                    <label for="full_name"><?= e(t('contact.full_name')) ?> *</label>
                    <input id="full_name" type="text" name="full_name" value="<?= e(old('full_name')) ?>" data-required data-label="<?= e(t('contact.full_name')) ?>">
                </div>

                <div class="form-group">
                    <label for="email"><?= e(t('contact.email')) ?> *</label>
                    <input id="email" type="email" name="email" value="<?= e(old('email')) ?>" data-required data-email data-label="<?= e(t('contact.email')) ?>">
                </div>

                <div class="form-group">
                    <label for="subject"><?= e(t('contact.subject')) ?> *</label>
                    <input id="subject" type="text" name="subject" value="<?= e(old('subject')) ?>" data-required data-label="<?= e(t('contact.subject')) ?>">
                </div>

                <div class="form-group">
                    <label for="message"><?= e(t('contact.message')) ?> *</label>
                    <textarea id="message" name="message" data-required data-label="<?= e(t('contact.message')) ?>"><?= e(old('message')) ?></textarea>
                </div>

                <div class="form-group checkbox">
                    <input id="gdpr_consent" type="checkbox" name="gdpr_consent" value="1" <?= old('gdpr_consent') === '1' ? 'checked' : '' ?> data-gdpr data-label="RGPD">
                    <label for="gdpr_consent"><?= e(t('contact.gdpr_label')) ?></label>
                </div>

                <p class="hint contact-required-note"><?= e(t('contact.required_note')) ?></p>
                <button type="submit" class="btn btn-primary"><?= e(t('contact.submit')) ?></button>
            </form>
        </article>

        <aside class="contact-side">
            <article class="card about-info-card contact-details-card" data-aos="fade-left" data-aos-delay="120">
                <h2><?= e(t('contact.channels_title')) ?></h2>
                <p class="hint"><?= e(t('contact.channels_intro')) ?></p>
                <p><strong>Email:</strong> <a href="mailto:<?= e($officialEmail) ?>"><?= e($officialEmail) ?></a></p>
                <p><strong><?= e(t('contact.location_label')) ?>:</strong> <?= e(t('site.event_location')) ?></p>
                <p><strong>Web:</strong> <a href="<?= e($siteDomain) ?>" target="_blank" rel="noopener"><?= e($siteDomain) ?></a></p>
                <ul class="contact-channel-list">
                    <li><span>LinkedIn</span><span><?= e(t('contact.soon')) ?></span></li>
                    <li><span>Facebook</span><span><?= e(t('contact.soon')) ?></span></li>
                    <li><span>X</span><span><?= e(t('contact.soon')) ?></span></li>
                </ul>
            </article>

            <article class="card about-info-card contact-map-card" data-aos="fade-left" data-aos-delay="200">
                <h3><?= e(t('contact.map_label')) ?></h3>
                <p><?= e(t('contact.map_intro')) ?></p>
                <a class="btn btn-secondary" href="https://maps.google.com/?q=Dortmund+Germany" target="_blank" rel="noopener"><?= e(t('contact.open_map')) ?></a>
            </article>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
