<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.home_title');
$pageDescription = t('seo.default_description');

$previewItems = program_preview($pdo, current_lang(), 4);
$partnerItems = fetch_active_partners($pdo, 6);
$partnerItems = array_values(array_filter($partnerItems, static function (array $partner): bool {
    $name = trim((string) ($partner['name'] ?? ''));
    $logoPath = trim((string) ($partner['logo_path'] ?? ''));
    return $name !== '' && $logoPath !== '';
}));
$programItems = fetch_program_items($pdo, current_lang());
$totalSessions = count($programItems);
$totalProgramDays = count(program_by_date($programItems));
$totalPartners = count($partnerItems);

require __DIR__ . '/includes/header.php';
?>

<section class="section about-hero home-about-hero">
    <div class="container about-hero-grid home-about-hero-grid">
        <div class="about-hero-copy" data-aos="fade-right">
            <p class="about-kicker"><?= e(t('site.short_name')) ?></p>
            <h1><?= e(t('home.hero_title')) ?></h1>
            <p class="lead"><?= e(t('home.hero_subtitle')) ?></p>
            <p><?= e(t('site.event_theme')) ?></p>
            <div class="cta-row">
                <a class="btn btn-light" href="<?= e(base_url('registration.php')) ?>"><?= e(t('buttons.register')) ?></a>
                <a class="btn btn-secondary" href="<?= e(base_url('contribute.php')) ?>"><?= e(t('buttons.contribute')) ?></a>
                <a class="btn btn-primary" href="<?= e(base_url('partners.php')) ?>"><?= e(t('buttons.become_partner')) ?></a>
            </div>
        </div>
        <aside class="about-hero-panel home-countdown-panel" data-aos="fade-left" data-aos-delay="120">
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

<section class="section about-stats-section home-stats-section">
    <div class="container">
        <div class="stats-strip about-stats-strip">
            <article class="stat-card about-stat-card" data-aos="zoom-in">
                <strong data-counter-end="<?= e((string) $totalSessions) ?>">0</strong>
                <span><?= e(t('home.stat_sessions')) ?></span>
            </article>
            <article class="stat-card about-stat-card" data-aos="zoom-in" data-aos-delay="100">
                <strong data-counter-end="<?= e((string) $totalProgramDays) ?>">0</strong>
                <span><?= e(t('home.stat_days')) ?></span>
            </article>
            <article class="stat-card about-stat-card" data-aos="zoom-in" data-aos-delay="200">
                <strong data-counter-end="<?= e((string) $totalPartners) ?>">0</strong>
                <span><?= e(t('home.stat_partners')) ?></span>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container grid-2 about-info-grid">
        <article class="card about-info-card" data-aos="fade-up">
            <h2><?= e(t('home.intro_title')) ?></h2>
            <p><?= e(t('home.intro_text')) ?></p>
        </article>
        <article class="card about-info-card" data-aos="fade-up" data-aos-delay="120">
            <h2><?= e(t('home.focus_title')) ?></h2>
            <p><?= e(t('home.focus_text')) ?></p>
        </article>
    </div>
</section>

<section class="section about-roadmap-section section-program">
    <div class="container">
        <div class="about-section-head" data-aos="fade-up">
            <h2><?= e(t('home.program_preview_title')) ?></h2>
            <p class="lead"><?= e(t('program.subtitle')) ?></p>
        </div>

        <div class="grid-2 about-info-grid program-preview-grid">
            <?php foreach ($previewItems as $index => $item): ?>
                <?php
                $itemType = preg_replace('/[^a-z0-9_-]/i', '', (string) ($item['item_type'] ?? 'conference')) ?: 'conference';
                $typeKey = 'program.' . $itemType;
                $eventDate = trim((string) ($item['event_date'] ?? ''));
                $startTime = substr(trim((string) ($item['start_time'] ?? '00:00:00')), 0, 8);
                $endTime = substr(trim((string) ($item['end_time'] ?? '23:59:59')), 0, 8);
                $calendarUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE';

                if ($eventDate !== '') {
                    try {
                        $timezone = (string) app_config('app.timezone', 'Europe/Berlin');
                        $startAt = new DateTimeImmutable($eventDate . ' ' . ($startTime !== '' ? $startTime : '00:00:00'), new DateTimeZone($timezone));
                        $endAt = new DateTimeImmutable($eventDate . ' ' . ($endTime !== '' ? $endTime : '23:59:59'), new DateTimeZone($timezone));

                        if ($endAt <= $startAt) {
                            $endAt = $startAt->modify('+1 hour');
                        }

                        $dates = $startAt->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z')
                            . '/' . $endAt->setTimezone(new DateTimeZone('UTC'))->format('Ymd\THis\Z');

                        $calendarUrl = 'https://calendar.google.com/calendar/render?' . http_build_query([
                            'action' => 'TEMPLATE',
                            'text' => (string) ($item['title'] ?? ''),
                            'dates' => $dates,
                            'details' => (string) ($item['description'] ?? ''),
                            'location' => (string) ($item['location'] ?? ''),
                            'ctz' => $timezone,
                        ]);
                    } catch (Throwable) {
                        // Keep fallback calendar URL.
                    }
                }

                $aosDelay = $index * 100;
                ?>
                <article class="card about-info-card program-card program-card--<?= e($itemType) ?>" data-aos="fade-up" data-aos-delay="<?= e((string) $aosDelay) ?>">
                    <h3><?= e((string) ($item['title'] ?? '')) ?></h3>
                    <div class="meta">
                        <span class="badge"><?= e((string) ($item['event_date'] ?? '')) ?></span>
                        <span class="badge"><?= e(substr((string) ($item['start_time'] ?? ''), 0, 5)) ?> - <?= e(substr((string) ($item['end_time'] ?? ''), 0, 5)) ?></span>
                        <span class="badge <?= e($itemType) ?>"><?= e(t($typeKey)) ?></span>
                    </div>
                    <p><?= e((string) ($item['description'] ?? '')) ?></p>
                    <p class="hint"><?= e(t('program.location')) ?>: <?= e((string) ($item['location'] ?? '')) ?></p>
                    <a class="program-card-action" href="<?= e($calendarUrl) ?>" target="_blank" rel="noopener"><?= e(t('home.add_to_calendar')) ?></a>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="program-main-cta-wrap" data-aos="fade-up" data-aos-delay="500"><a class="btn btn-primary program-main-cta" href="<?= e(base_url('program.php')) ?>"><?= e(t('home.see_full_program')) ?></a></p>
    </div>
</section>

<section class="section section-partners">
    <div class="container">
        <?php $hasPartners = !empty($partnerItems); ?>
        <div class="about-section-head" data-aos="fade-up">
            <h2><?= e($hasPartners ? t('home.partners_preview_title') : t('partners.hero_title')) ?></h2>
            <p class="lead"><?= e($hasPartners ? t('partners.open_text') : t('partners.hero_subtitle')) ?></p>
        </div>
        <?php if ($hasPartners): ?>
            <div class="grid-3 partners-logo-grid">
                <?php foreach ($partnerItems as $index => $partner): ?>
                    <?php
                    $partnerName = trim((string) ($partner['name'] ?? 'Partner'));
                    $websiteUrl = trim((string) ($partner['website_url'] ?? ''));
                    $logoPath = trim((string) ($partner['logo_path'] ?? ''));
                    $logoUrl = '';
                    if ($logoPath !== '') {
                        $logoUrl = preg_match('#^https?://#i', $logoPath) === 1 ? $logoPath : base_url(ltrim($logoPath, '/'));
                    }
                    $showWebsite = $websiteUrl !== '' && $websiteUrl !== '#';

                    $partnerDelay = 100 + ($index * 80);
                    ?>
                    <article class="card about-info-card partner-logo-card" data-aos="fade-up" data-aos-delay="<?= e((string) $partnerDelay) ?>">
                        <div class="partner-logo-link">
                            <div class="partner-logo-svg-wrap" aria-hidden="true">
                                <?php if ($logoUrl !== ''): ?>
                                    <img class="partner-logo-image" src="<?= e($logoUrl) ?>" alt="<?= e($partnerName) ?>">
                                <?php else: ?>
                                    <span class="partner-logo-placeholder"><?= e(t('partners.logo_pending')) ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="partner-logo-name"><?= e($partnerName) ?></span>
                            <?php if ($showWebsite): ?>
                                <a class="partner-preview-link" href="<?= e($websiteUrl) ?>" target="_blank" rel="noopener"><?= e(t('partners.visit_site')) ?></a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <article class="card about-info-card partners-empty-state--compact" data-aos="fade-up" data-aos-delay="80">
                <p><?= e(t('partners.hero_subtitle')) ?></p>
            </article>
        <?php endif; ?>
        <p data-aos="fade-up" data-aos-delay="420">
            <a class="btn btn-secondary" href="<?= e(base_url('partners.php')) ?>">
                <?= e($hasPartners ? t('home.see_all_partners') : t('buttons.become_partner')) ?>
            </a>
        </p>
    </div>
</section>

<section class="section">
    <div class="container grid-2 about-message-grid">
        <article class="card about-info-card home-contact-card" data-aos="fade-right">
            <h2><?= e(t('home.contact_title')) ?></h2>
            <p><?= e(t('home.contact_text')) ?></p>
        </article>
        <article class="card about-info-card home-cta-card" data-aos="fade-left" data-aos-delay="120">
            <h2><?= e(t('about.cta_title')) ?></h2>
            <p><?= e(t('about.cta_text')) ?></p>
            <div class="cta-row">
                <a class="btn btn-light" href="<?= e(base_url('contact.php')) ?>"><?= e(t('home.contact_cta')) ?></a>
                <a class="btn btn-primary" href="<?= e(base_url('registration.php')) ?>"><?= e(t('buttons.register')) ?></a>
            </div>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
