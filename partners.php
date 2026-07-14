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
        redirect('partners');
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
    if ($sponsorshipLevel !== '' && !array_key_exists($sponsorshipLevel, $sponsorLevels)) {
        $errors[] = t('validation.required');
    }
    if ($gdprConsent !== 1) {
        $errors[] = t('validation.gdpr');
    }

    if (!empty($errors)) {
        set_flash('error', implode(' ', array_unique($errors)));
        redirect('partners');
    }

    /* ── Logo upload ─────────────────────────────────────────────────────── */
    $logoPath = null;
    if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'image/gif'];
        $allowedExt  = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['logo']['tmp_name']);
        $origExt = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));

        if (in_array($mime, $allowedMime, true) && in_array($origExt, $allowedExt, true) && $_FILES['logo']['size'] <= 2 * 1024 * 1024) {
            $uploadDir = ROOT_PATH . '/assets/images/partners/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            $slug = preg_replace('/[^a-z0-9]+/', '_', strtolower($organizationName));
            $slug = trim($slug, '_');
            $filename = $slug . '_' . uniqid() . '.' . $origExt;
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadDir . $filename)) {
                $logoPath = 'assets/images/partners/' . $filename;
            }
        }
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO sponsor_requests
            (organization_name, contact_person, email, phone, website, sponsorship_level, message, gdpr_consent, language, logo_path, created_at)
            VALUES
            (:organization_name, :contact_person, :email, :phone, :website, :sponsorship_level, :message, :gdpr_consent, :language, :logo_path, :created_at)');
        $stmt->execute([
            'organization_name' => $organizationName,
            'contact_person' => $contactPerson,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'website' => $website !== '' ? $website : null,
            'sponsorship_level' => $sponsorshipLevel,
            'message' => $message !== '' ? $message : null,
            'gdpr_consent' => $gdprConsent,
            'language' => current_lang(),
            'logo_path' => $logoPath,
            'created_at' => db_now(),
        ]);
    } catch (Throwable) {
        set_flash('error', 'Erreur technique. Merci de réessayer.');
        redirect('partners');
    }

    try {
        $stmt = $pdo->prepare('INSERT INTO sponsor_requests
            (organization_name, contact_person, email, phone, website, sponsorship_level, message, gdpr_consent, language)
            VALUES
            (:organization_name, :contact_person, :email, :phone, :website, :sponsorship_level, :message, :gdpr_consent, :language)');
        $stmt->execute([
            'organization_name' => $organizationName,
            'contact_person' => $contactPerson,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'website' => $website !== '' ? $website : null,
            'sponsorship_level' => $sponsorshipLevel,
            'message' => $message !== '' ? $message : null,
            'gdpr_consent' => $gdprConsent,
            'language' => current_lang(),
        ]);
    } catch (Throwable) {
        set_flash('error', 'Erreur technique. Merci de reessayer.');
        redirect('partners.php');
    }

    $organizerEmail = get_setting($pdo, 'organizer_email', 'contact@ugfa-ev.org');
    $subject = t('emails.sponsor_subject');
    $body = '<p>Organisation: ' . e($organizationName) . '</p>'
        . '<p>Contact: ' . e($contactPerson) . '</p>'
        . '<p>Email: ' . e($email) . '</p>'
        . '<p>Phone: ' . e($phone) . '</p>'
        . '<p>Website: ' . e($website) . '</p>'
        . '<p>Level: ' . e($sponsorshipLevel) . '</p>'
        . '<p>Message: ' . nl2br(e($message)) . '</p>'
        . ($logoPath ? '<p>Logo: <img src="' . e(base_url($logoPath)) . '" style="max-height:80px"></p>' : '');
    send_email($organizerEmail, 'Organisation', $subject, $body, strip_tags($body));

    clear_old_input();
    set_flash('success', t('partners.success'));
    redirect('partners');
}

$benefits = [
    ['title' => t('partners.benefit_visibility_title'), 'text' => t('partners.benefit_visibility_text')],
    ['title' => t('partners.benefit_network_title'), 'text' => t('partners.benefit_network_text')],
    ['title' => t('partners.benefit_reputation_title'), 'text' => t('partners.benefit_reputation_text')],
    ['title' => t('partners.benefit_impact_title'), 'text' => t('partners.benefit_impact_text')],
];
$targetTypes = [
    t('partners.target_institutional'),
    t('partners.target_companies'),
    t('partners.target_finance'),
    t('partners.target_associations'),
    t('partners.target_media'),
    t('partners.target_education'),
];

require __DIR__ . '/includes/header.php';
?>

<section class="section partners-intro-section">
    <div class="container">
        <div class="about-section-head partners-intro-shell" data-aos="fade-up">
            <h1><?= e(t('partners.hero_title')) ?></h1>
            <p class="lead"><?= e(t('partners.hero_subtitle')) ?></p>
            <p class="partners-hero-text"><?= e(t('partners.open_text')) ?></p>
        </div>
    </div>
</section>

<section class="section about-roadmap-section">
    <div class="container partners-form-container">
        <div class="form-card partners-form-card" data-aos="fade-up">
            <div class="partners-form-head">
                <h2><?= e(t('partners.become_sponsor')) ?></h2>
                <p class="hint"><?= e(t('partners.form_intro')) ?></p>
            </div>

            <form method="post" action="<?= e(base_url('partners')) ?>" enctype="multipart/form-data" data-validate novalidate>
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

                <!-- Logo upload -->
                <div class="form-group">
                    <label><?= current_lang() === 'de' ? 'Logo Ihrer Organisation' : 'Logo de votre organisation' ?></label>
                    <div class="logo-upload-zone" id="logoDropZone">
                        <input id="logo" type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/svg+xml,image/gif" class="logo-upload-input">
                        <div class="logo-upload-placeholder" id="logoPlaceholder">
                            <div class="logo-upload-icon">
                                <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
                                    <rect width="48" height="48" rx="12" fill="#eef3ff"/>
                                    <path d="M24 14v14M24 14l-5 5M24 14l5 5" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="10" y="32" width="28" height="4" rx="2" fill="#2563eb" opacity=".15"/>
                                    <path d="M10 34h28" stroke="#2563eb" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <p class="logo-upload-title"><?= current_lang() === 'de' ? 'Logo hier ablegen oder' : 'Déposez votre logo ici ou' ?> <span class="logo-upload-cta"><?= current_lang() === 'de' ? 'auswählen' : 'parcourir' ?></span></p>
                            <p class="logo-upload-hint">PNG, JPG, SVG, WebP — max 2 Mo</p>
                        </div>
                        <div class="logo-upload-preview" id="logoPreview" hidden>
                            <img id="logoPreviewImg" src="" alt="Aperçu logo">
                            <button type="button" class="logo-upload-remove" id="logoRemoveBtn" aria-label="Supprimer">
                                <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6 6l8 8M14 6l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </button>
                            <p class="logo-upload-filename" id="logoFilename"></p>
                        </div>
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

                <p class="hint partners-required-note"><?= e(t('partners.required_note')) ?></p>
                <button type="submit" class="btn btn-hero-partner"><?= e(t('partners.submit')) ?></button>
            </form>
            <script>
            (function(){
                var zone = document.getElementById('logoDropZone');
                var input = document.getElementById('logo');
                var placeholder = document.getElementById('logoPlaceholder');
                var preview = document.getElementById('logoPreview');
                var previewImg = document.getElementById('logoPreviewImg');
                var filename = document.getElementById('logoFilename');
                var removeBtn = document.getElementById('logoRemoveBtn');

                function showPreview(file) {
                    if (!file || !file.type.startsWith('image/')) return;
                    var url = URL.createObjectURL(file);
                    previewImg.src = url;
                    filename.textContent = file.name;
                    placeholder.hidden = true;
                    preview.hidden = false;
                    zone.classList.add('logo-upload-zone--has-file');
                }

                function clearPreview() {
                    previewImg.src = '';
                    filename.textContent = '';
                    placeholder.hidden = false;
                    preview.hidden = true;
                    zone.classList.remove('logo-upload-zone--has-file');
                    input.value = '';
                }

                zone.addEventListener('click', function(e) {
                    if (!e.target.closest('#logoRemoveBtn')) { input.click(); }
                });
                input.addEventListener('change', function() {
                    if (input.files && input.files[0]) { showPreview(input.files[0]); }
                });
                removeBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    clearPreview();
                });
                zone.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    zone.classList.add('logo-upload-zone--drag');
                });
                zone.addEventListener('dragleave', function() {
                    zone.classList.remove('logo-upload-zone--drag');
                });
                zone.addEventListener('drop', function(e) {
                    e.preventDefault();
                    zone.classList.remove('logo-upload-zone--drag');
                    var files = e.dataTransfer.files;
                    if (files && files[0]) {
                        input.files = files;
                        showPreview(files[0]);
                    }
                });
            })();
            </script>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="about-section-head" data-aos="fade-up">
            <h2><?= e(t('partners.why_title')) ?></h2>
            <p class="lead"><?= e(t('partners.why_intro')) ?></p>
        </div>

        <div class="grid-2 partners-benefits-grid">
            <?php foreach ($benefits as $index => $benefit): ?>
                <?php $benefitDelay = ($index % 4) * 90; ?>
                <article class="card about-info-card partners-benefit-card" data-aos="fade-up" data-aos-delay="<?= e((string) $benefitDelay) ?>">
                    <h3><?= e((string) $benefit['title']) ?></h3>
                    <p><?= e((string) $benefit['text']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section about-roadmap-section partners-target-section">
    <div class="container">
        <div class="about-section-head" data-aos="fade-up">
            <h2><?= e(t('partners.target_title')) ?></h2>
            <p class="lead"><?= e(t('partners.target_intro')) ?></p>
        </div>

        <div class="grid-2 partners-target-grid">
            <?php foreach ($targetTypes as $index => $targetType): ?>
                <?php $targetDelay = ($index % 4) * 80; ?>
                <article class="about-roadmap-step partners-target-card" data-aos="fade-up" data-aos-delay="<?= e((string) $targetDelay) ?>">
                    <span class="about-step-index"><?= e(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)) ?></span>
                    <p><?= e($targetType) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
