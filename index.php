<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.home_title');
$pageDescription = t('seo.default_description');

$previewItems = program_preview($pdo, current_lang(), 4);
$partnerItems = fetch_active_partners($pdo, 6);

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <h1><?= e(t('home.hero_title')) ?></h1>
            <p><?= e(t('home.hero_subtitle')) ?></p>
            <p><?= e(t('site.event_theme')) ?></p>
            <div class="cta-row">
                <a class="btn btn-light" href="<?= e(base_url('registration.php')) ?>"><?= e(t('buttons.register')) ?></a>
                <a class="btn btn-secondary" href="<?= e(base_url('contribute.php')) ?>"><?= e(t('buttons.contribute')) ?></a>
                <a class="btn btn-primary" href="<?= e(base_url('partners.php')) ?>"><?= e(t('buttons.become_partner')) ?></a>
            </div>
        </div>
        <aside class="hero-card">
            <h2><?= e(t('home.countdown_title')) ?></h2>
            <div class="countdown-grid" data-countdown>
                <div class="countdown-box">
                    <strong data-days>0</strong>
                    <span><?= e(t('home.countdown_days_label')) ?></span>
                </div>
                <div class="countdown-box">
                    <strong data-hours>00</strong>
                    <span><?= e(t('home.countdown_hours_label')) ?></span>
                </div>
                <div class="countdown-box">
                    <strong data-minutes>00</strong>
                    <span><?= e(t('home.countdown_minutes_label')) ?></span>
                </div>
                <div class="countdown-box">
                    <strong data-seconds>00</strong>
                    <span><?= e(t('home.countdown_seconds_label')) ?></span>
                </div>
            </div>
        </aside>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2><?= e(t('home.intro_title')) ?></h2>
        <p class="lead"><?= e(t('home.intro_text')) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2><?= e(t('home.program_preview_title')) ?></h2>
        <div class="grid-2">
            <?php foreach ($previewItems as $item): ?>
                <?php $typeKey = 'program.' . ((string) ($item['item_type'] ?? 'conference')); ?>
                <article class="card">
                    <h3><?= e((string) ($item['title'] ?? '')) ?></h3>
                    <div class="meta">
                        <span class="badge"><?= e((string) ($item['event_date'] ?? '')) ?></span>
                        <span class="badge"><?= e(substr((string) ($item['start_time'] ?? ''), 0, 5)) ?> - <?= e(substr((string) ($item['end_time'] ?? ''), 0, 5)) ?></span>
                        <span class="badge <?= e((string) ($item['item_type'] ?? 'conference')) ?>"><?= e(t($typeKey)) ?></span>
                    </div>
                    <p><?= e((string) ($item['description'] ?? '')) ?></p>
                    <p class="hint"><?= e(t('program.location')) ?>: <?= e((string) ($item['location'] ?? '')) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <p><a class="btn btn-primary" href="<?= e(base_url('program.php')) ?>"><?= e(t('home.see_full_program')) ?></a></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2><?= e(t('home.partners_preview_title')) ?></h2>
        <div class="grid-3">
            <?php foreach ($partnerItems as $partner): ?>
                <article class="card">
                    <a href="<?= e((string) ($partner['website_url'] ?? '#')) ?>" target="_blank" rel="noopener">
                        <div class="partner-logo">
                            <?= e((string) ($partner['name'] ?? 'Partner')) ?>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
        <p><a class="btn btn-secondary" href="<?= e(base_url('partners.php')) ?>"><?= e(t('home.see_all_partners')) ?></a></p>
    </div>
</section>

<section class="section">
    <div class="container card">
        <p><?= e(t('home.contact_block')) ?></p>
        <a class="btn btn-light" href="<?= e(base_url('contact.php')) ?>"><?= e(t('nav.contact')) ?></a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
