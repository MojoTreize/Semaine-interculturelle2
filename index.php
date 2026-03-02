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

<section class="section" data-aos="fade-up">
    <div class="container">
        <h2><?= e(t('home.intro_title')) ?></h2>
        <p class="lead"><?= e(t('home.intro_text')) ?></p>
    </div>
</section>

<section class="section section-program" data-aos="fade-up">
    <div class="container">
        <h2><?= e(t('home.program_preview_title')) ?></h2>
        <div class="grid-2 program-preview-grid">
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
                <article class="card program-card program-card--<?= e($itemType) ?>" data-aos="fade-up" data-aos-delay="<?= e((string) $aosDelay) ?>">
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

<section class="section section-partners" data-aos="fade-up">
    <div class="container">
        <h2><?= e(t('home.partners_preview_title')) ?></h2>
        <div class="grid-3 partners-logo-grid">
            <?php foreach ($partnerItems as $index => $partner): ?>
                <?php
                $partnerName = trim((string) ($partner['name'] ?? 'Partner'));
                $websiteUrl = trim((string) ($partner['website_url'] ?? '#'));
                $words = preg_split('/\s+/', $partnerName) ?: [];
                $initials = '';
                foreach ($words as $word) {
                    if ($word === '') {
                        continue;
                    }

                    $initials .= strtoupper(substr($word, 0, 1));
                    if (strlen($initials) >= 2) {
                        break;
                    }
                }

                if ($initials === '') {
                    $initials = 'PT';
                }

                $partnerDelay = 100 + ($index * 80);
                ?>
                <article class="card partner-logo-card" data-aos="fade-up" data-aos-delay="<?= e((string) $partnerDelay) ?>">
                    <a class="partner-logo-link" href="<?= e($websiteUrl !== '' ? $websiteUrl : '#') ?>" target="_blank" rel="noopener">
                        <div class="partner-logo-svg-wrap" aria-hidden="true">
                            <svg class="partner-logo-svg" viewBox="0 0 220 120" role="img" aria-hidden="true">
                                <rect x="1" y="1" width="218" height="118" rx="18" fill="#f5f7ff" stroke="#d7dcef" />
                                <circle cx="44" cy="60" r="18" fill="#c62828" />
                                <rect x="72" y="45" width="64" height="30" rx="8" fill="#1f8f46" />
                                <path d="M148 78l23-36 22 36h-45Z" fill="#f4b400" />
                                <text x="110" y="69" text-anchor="middle" fill="#1d2a40" font-size="30" font-weight="700" font-family="Segoe UI, Arial, sans-serif"><?= e($initials) ?></text>
                            </svg>
                        </div>
                        <span class="partner-logo-name"><?= e($partnerName) ?></span>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>
        <p data-aos="fade-up" data-aos-delay="420"><a class="btn btn-secondary" href="<?= e(base_url('partners.php')) ?>"><?= e(t('home.see_all_partners')) ?></a></p>
    </div>
</section>

<section class="section" data-aos="fade-up">
    <div class="container card" data-aos="fade-up">
        <p><?= e(t('home.contact_block')) ?></p>
        <a class="btn btn-light" href="<?= e(base_url('contact.php')) ?>"><?= e(t('nav.contact')) ?></a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
