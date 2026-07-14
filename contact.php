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
        redirect('contact');
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
        redirect('contact');
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO contact_messages
            (full_name, email, subject, message, gdpr_consent, language, created_at)
            VALUES
            (:full_name, :email, :subject, :message, :gdpr_consent, :language, :created_at)');
        $stmt->execute([
            'full_name' => $fullName,
            'email' => $email,
            'subject' => $subject,
            'message' => $message,
            'gdpr_consent' => $gdprConsent,
            'language' => current_lang(),
            'created_at' => db_now(),
        ]);
    } catch (Throwable) {
        set_flash('error', 'Erreur technique. Merci de réessayer.');
        redirect('contact');
    }

    $organizerEmail = get_setting($pdo, 'organizer_email', 'contact@ugfa-ev.org');
    $mailSubject = t('emails.contact_subject');
    $mailBody = '<p>Nom: ' . e($fullName) . '</p>'
        . '<p>Email: ' . e($email) . '</p>'
        . '<p>Objet: ' . e($subject) . '</p>'
        . '<p>Message:<br>' . nl2br(e($message)) . '</p>';
    send_email($organizerEmail, 'Organisation', $mailSubject, $mailBody, strip_tags($mailBody));

    clear_old_input();
    set_flash('success', t('contact.success'));
    redirect('contact');
}

$officialEmail = get_setting($pdo, 'contact_email', 'contact@ugfa-ev.org');
$siteDomain    = get_setting($pdo, 'site_domain', base_url(''));
$waRaw         = trim((string) get_setting($pdo, 'whatsapp_number', ''));
$waNum         = preg_replace('/[^0-9+]/', '', $waRaw);
$waUrl         = $waNum !== '' ? 'https://wa.me/' . ltrim($waNum, '+') . '?text=' . rawurlencode('Bonjour, j\'ai une question concernant l\'événement UGFA Dortmund 2026.') : '';

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

            <form method="post" action="<?= e(base_url('contact')) ?>" data-validate novalidate>
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
                <p><strong><?= e(t('contact.location_label')) ?>:</strong>
                    <a class="program-location-link" href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode((string) t('site.event_location')) ?>" target="_blank" rel="noopener noreferrer"><?= e(t('site.event_location')) ?></a>
                </p>
                <p><strong>Web:</strong> <a href="<?= e($siteDomain) ?>" target="_blank" rel="noopener"><?= e($siteDomain) ?></a></p>
                <ul class="contact-channel-list">
                    <li>
                        <span>Facebook</span>
                        <a href="https://www.facebook.com/profile.php?id=61591357127241"
                           target="_blank" rel="noopener noreferrer"
                           style="font-weight:600;color:#1877F2;text-decoration:none">
                            Nous suivre →
                        </a>
                    </li>
                </ul>
            </article>

            <article class="card about-info-card contribute-whatsapp-card" data-aos="fade-left" data-aos-delay="200">
                <h3 class="contribute-whatsapp-title">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                         style="width:20px;height:20px;color:#25D366;flex-shrink:0">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Contacter via WhatsApp
                </h3>
                <p class="hint">Pour toute question rapide ou en cas de problème, notre équipe est joignable directement via WhatsApp.</p>
                <?php if ($waUrl !== ''): ?>
                    <a href="<?= e($waUrl) ?>" class="btn-whatsapp" target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"
                             style="width:18px;height:18px;flex-shrink:0">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                        Contacter via WhatsApp
                    </a>
                <?php else: ?>
                    <p class="hint" style="font-size:.85rem">
                        Numéro WhatsApp non encore configuré.<br>
                        Configurez-le dans <a href="<?= e(admin_url('settings.php')) ?>">l'espace admin</a>.
                    </p>
                <?php endif; ?>
            </article>
        </aside>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>

