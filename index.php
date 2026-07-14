<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.home_title');
$pageDescription = t('seo.default_description');

$previewItems = program_preview($pdo, current_lang(), 4);
try {
    $partnerItems = $pdo->query(
        "SELECT id, name, website_url, logo_path, sponsorship_level
         FROM partners
         WHERE partner_type = 'sponsor' AND is_active = 1
         ORDER BY display_order ASC, id ASC
         LIMIT 6"
    )->fetchAll();
} catch (Throwable) {
    $partnerItems = [];
}
$programItems = fetch_program_items($pdo, current_lang());
$totalSessions = count($programItems);
$totalProgramDays = count(program_by_date($programItems));
$totalPartners = count($partnerItems);

require __DIR__ . '/includes/header.php';
?>

<section class="home-hero" data-aos="fade">
    <div class="home-hero-media">
        <img src="<?= e(base_url('assets/images/photos/hero-monument.png')) ?>" alt="<?= e(t('site.short_name')) ?>" class="home-hero-img">
        <span class="home-hero-scrim" aria-hidden="true"></span>
    </div>
    <div class="container home-hero-inner">
        <div class="home-hero-copy" data-aos="fade-right" data-aos-delay="60">
            <p class="home-hero-kicker">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.1 6.6.9-4.8 4.6 1.2 6.6L12 17.9 6.1 20.2l1.2-6.6L2.5 9l6.6-.9L12 2z"/></svg>
                <?= e(t('home.edition_badge')) ?>
            </p>
            <h1><?= e(t('home.hero_title')) ?></h1>
            <p class="home-hero-facts">
                <span><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s7-6.1 7-11a7 7 0 10-14 0c0 4.9 7 11 7 11z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.4" stroke="currentColor" stroke-width="1.7"/></svg><?= e(t('home.hero_location')) ?></span>
                <span><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="4.5" width="17" height="16" rx="2.4" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 9h17M8 3v3M16 3v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg><?= e(t('site.event_dates')) ?></span>
            </p>
            <p class="home-hero-lead"><?= e(t('site.event_theme')) ?></p>
            <div class="cta-row">
                <a class="btn btn-hero-partner" href="<?= e(base_url('partners.php')) ?>">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" style="width:18px;height:18px;flex-shrink:0"><path d="M15 12c0 1.66-1.34 3-3 3s-3-1.34-3-3 1.34-3 3-3 3 1.34 3 3z" stroke="currentColor" stroke-width="1.8"/><path d="M3.5 17.5C4.5 15.5 7 14 12 14s7.5 1.5 8.5 3.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><path d="M17 6l2 2-2 2M7 6L5 8l2 2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <?= e(t('buttons.become_partner')) ?>
                </a>
                <a class="btn btn-hero-contribute" href="<?= e(base_url('contribute.php')) ?>">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" style="width:18px;height:18px;flex-shrink:0"><path d="M12 21C12 21 4 15.5 4 9.5A5 5 0 0 1 12 6a5 5 0 0 1 8 3.5C20 15.5 12 21 12 21z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
                    <?= e(t('buttons.contribute')) ?>
                </a>
                <a class="btn btn-hero-register" href="<?= e(base_url('registration.php')) ?>">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" style="width:18px;height:18px;flex-shrink:0"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/></svg>
                    <?= e(t('buttons.register')) ?>
                </a>
            </div>
        </div>
        <aside class="home-countdown" data-aos="fade-left" data-aos-delay="140">
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

<section class="section home-stats-section">
    <div class="container">
        <div class="home-stats-card" data-aos="fade-up">
            <article class="home-stat">
                <span class="home-stat-icon home-stat-icon--green" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><rect x="3.5" y="4.5" width="17" height="16" rx="2.4" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 9h17M8 3v3M16 3v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <strong data-counter-end="<?= e((string) $totalSessions) ?>">0</strong>
                    <span><?= e(t('home.stat_sessions')) ?></span>
                </div>
            </article>
            <article class="home-stat">
                <span class="home-stat-icon home-stat-icon--gold" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="M12 7.5V12l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <strong data-counter-end="<?= e((string) $totalProgramDays) ?>">0</strong>
                    <span><?= e(t('home.stat_days')) ?></span>
                </div>
            </article>
            <article class="home-stat">
                <span class="home-stat-icon home-stat-icon--red" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><circle cx="9" cy="9" r="3" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 19a5.5 5.5 0 0111 0M16 8.5a3 3 0 010 5M20.5 19a5.2 5.2 0 00-3.2-4.8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <strong data-counter-end="<?= e((string) $totalPartners) ?>">0</strong>
                    <span><?= e(t('home.stat_partners')) ?></span>
                </div>
            </article>
        </div>
    </div>
</section>

<?php
$homeFeatures = [
    [
        'variant' => 'green',
        'title' => t('home.feature_region_title'),
        'text' => t('home.feature_region_text'),
        'link' => t('home.feature_region_link'),
        'href' => 'https://zalymerveille.com/guinea',
        'target' => '_blank',
        'image' => base_url('assets/images/photos/region.jpg'),
        'cover' => true,
        'icon' => '<path d="M4 18c3-6 5-9 8-9s5 3 8 9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M12 9V4M9.5 6.5L12 4l2.5 2.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
    ],
    [
        'variant' => 'gold',
        'title' => t('home.feature_dialogue_title'),
        'text' => t('home.feature_dialogue_text'),
        'link' => t('home.feature_dialogue_link'),
        'href' => base_url('program.php'),
        'image' => base_url('assets/images/photos/dialogue.jpg'),
        'cover' => true,
        'icon' => '<circle cx="9" cy="9" r="2.6" stroke="currentColor" stroke-width="1.7"/><circle cx="16" cy="10" r="2.2" stroke="currentColor" stroke-width="1.7"/><path d="M3.8 19a5.2 5.2 0 0110.4 0M15 14.6a4.4 4.4 0 015.2 4.4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
    ],
    [
        'variant' => 'red',
        'title' => t('home.feature_heritage_title'),
        'text' => t('home.feature_heritage_text'),
        'link' => t('home.feature_heritage_link'),
        'href' => 'https://zalymerveille.com/museums?q=NZEREKORE',
        'target' => '_blank',
        'image' => base_url('assets/images/photos/patrimoine.jpg'),
        'cover' => true,
        'icon' => '<path d="M4 20h16M5 20V10l7-5 7 5v10M9 20v-5h6v5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
    ],
];
?>
<section class="section home-features-section">
    <div class="container home-features-grid">
        <?php foreach ($homeFeatures as $fi => $feature): ?>
            <article class="home-feature-card home-feature-card--<?= e($feature['variant']) ?>" data-aos="fade-up" data-aos-delay="<?= e((string) ($fi * 120)) ?>">
                <div class="home-feature-media<?= !empty($feature['image']) ? ' home-feature-media--photo' : '' ?><?= !empty($feature['cover']) ? ' home-feature-media--cover' : '' ?>"<?= !empty($feature['image']) ? ' style="--feat-img: url(\'' . e($feature['image']) . '\');"' : '' ?> aria-hidden="true">
                    <span class="home-feature-icon">
                        <svg viewBox="0 0 24 24" fill="none"><?= $feature['icon'] ?></svg>
                    </span>
                </div>
                <div class="home-feature-body">
                    <h3><?= e($feature['title']) ?></h3>
                    <p><?= e($feature['text']) ?></p>
                    <a class="home-feature-link" href="<?= e($feature['href']) ?>"<?= !empty($feature['target']) ? ' target="' . e($feature['target']) . '" rel="noopener noreferrer"' : '' ?>>
                        <?= e($feature['link']) ?>
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php
$monthsAbbr = [1 => 'JAN', 2 => 'FEV', 3 => 'MAR', 4 => 'AVR', 5 => 'MAI', 6 => 'JUIN', 7 => 'JUIL', 8 => 'AOU', 9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DEC'];

// Modern Lucide-style stroke icons per program type (reuses the project's inline-SVG convention).
$programIcons = [
    // Ceremony: microphone (podium / official opening)
    'ceremony' => '<rect x="9" y="2" width="6" height="11" rx="3" stroke="currentColor" stroke-width="1.7"/><path d="M5 10v1a7 7 0 0 0 14 0v-1M12 18v4M8 22h8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
    // Conference: growth / trending-up (Simandou 2040 - sustainable development)
    'conference' => '<path d="M22 7 13.5 15.5 8.5 10.5 2 17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 7h6v6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
    // Panel: discussion (diaspora-investment dialogue)
    'panel' => '<path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>',
    // Exhibition: image / gallery
    'exhibition' => '<rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.7"/><circle cx="9" cy="9" r="2" stroke="currentColor" stroke-width="1.7"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
    // Networking: people / community
    'networking' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.7"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
    // Workshop: lightbulb / ideas
    'workshop' => '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1.3.5 2.6 1.5 3.5.8.8 1.3 1.5 1.5 2.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 18h6M10 22h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
];
?>
<section class="section section-program home-program-section">
    <div class="container home-program-layout">
        <div class="home-program-intro" data-aos="fade-right">
            <p class="home-kicker"><?= e(t('home.program_kicker')) ?></p>
            <h2><?= e(t('home.program_intro_title')) ?></h2>
            <p class="lead"><?= e(t('home.program_intro_text')) ?></p>
            <a class="btn btn-secondary home-program-cta" href="<?= e(base_url('program.php')) ?>"><?= e(t('home.see_full_program')) ?></a>
        </div>

        <div class="prog-slider-shell" data-aos="fade-left" data-aos-delay="120">
            <div class="prog-slider" id="progSlider">
                <div class="prog-slider-track" id="progSliderTrack">
                    <?php foreach ($previewItems as $index => $item): ?>
                        <?php
                        $itemType   = preg_replace('/[^a-z0-9_-]/i', '', (string) ($item['item_type'] ?? 'conference')) ?: 'conference';
                        $typeKey    = 'program.' . $itemType;
                        $eventDate  = trim((string) ($item['event_date'] ?? ''));
                        $dayNum     = '';
                        $monthLabel = '';
                        if ($eventDate !== '') {
                            try {
                                $d = new DateTimeImmutable($eventDate);
                                $dayNum     = $d->format('d');
                                $monthLabel = $monthsAbbr[(int) $d->format('n')] ?? strtoupper($d->format('M'));
                            } catch (Throwable) {}
                        }
                        $programIcon = $programIcons[$itemType] ?? $programIcons['conference'];
                        $colorMap = ['ceremony'=>'#c62828','conference'=>'#1565c0','panel'=>'#2e7d32','exhibition'=>'#6a1b9a','networking'=>'#e65100','workshop'=>'#00838f'];
                        $accentColor = $colorMap[$itemType] ?? '#1565c0';
                        ?>
                        <article class="prog-slide program-card--<?= e($itemType) ?>" style="--slide-accent:<?= e($accentColor) ?>">
                            <div class="prog-slide-top">
                                <div class="prog-slide-icon-wrap">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><?= $programIcon ?></svg>
                                </div>
                                <?php if ($dayNum !== ''): ?>
                                    <div class="prog-slide-date">
                                        <strong><?= e($dayNum) ?></strong>
                                        <span><?= e($monthLabel) ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="prog-slide-body">
                                <span class="prog-slide-badge"><?= e(t($typeKey)) ?></span>
                                <h3><?= e((string) ($item['title'] ?? '')) ?></h3>
                                <p><?= e((string) ($item['description'] ?? '')) ?></p>
                            </div>
                            <div class="prog-slide-footer">
                                <span class="prog-slide-time">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" style="width:14px;height:14px"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.7"/><path d="M12 7.5V12l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                                    <?= e(substr((string) ($item['start_time'] ?? ''), 0, 5)) ?> – <?= e(substr((string) ($item['end_time'] ?? ''), 0, 5)) ?>
                                </span>
                                <span class="prog-slide-loc">
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" style="width:14px;height:14px"><path d="M12 21s7-6.1 7-11a7 7 0 10-14 0c0 4.9 7 11 7 11z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.2" stroke="currentColor" stroke-width="1.7"/></svg>
                                    <?= e((string) ($item['location'] ?? '')) ?>
                                </span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Contrôles -->
            <div class="prog-slider-controls">
                <button class="prog-slider-btn" id="progPrev" aria-label="Précédent">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M15 6l-6 6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <div class="prog-slider-dots" id="progDots">
                    <?php for ($i = 0; $i < count($previewItems); $i++): ?>
                        <button class="prog-dot<?= $i === 0 ? ' is-active' : '' ?>" data-index="<?= $i ?>" aria-label="Diapositive <?= $i + 1 ?>"></button>
                    <?php endfor; ?>
                </div>
                <button class="prog-slider-btn" id="progNext" aria-label="Suivant">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    var track  = document.getElementById('progSliderTrack');
    var prev   = document.getElementById('progPrev');
    var next   = document.getElementById('progNext');
    var dots   = document.querySelectorAll('.prog-dot');
    if (!track || !prev || !next) return;

    var slides = track.querySelectorAll('.prog-slide');
    var total  = slides.length;
    var cur    = 0;
    var auto, startX;

    function goTo(n) {
        cur = (n + total) % total;
        track.style.transform = 'translateX(-' + (cur * 100) + '%)';
        dots.forEach(function (d, i) { d.classList.toggle('is-active', i === cur); });
    }

    prev.addEventListener('click', function () { goTo(cur - 1); resetAuto(); });
    next.addEventListener('click', function () { goTo(cur + 1); resetAuto(); });
    dots.forEach(function (d) {
        d.addEventListener('click', function () { goTo(+d.dataset.index); resetAuto(); });
    });

    /* Swipe mobile */
    track.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    track.addEventListener('touchend', function (e) {
        var diff = startX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 40) { goTo(diff > 0 ? cur + 1 : cur - 1); resetAuto(); }
    }, { passive: true });

    /* Autoplay */
    function startAuto() { auto = setInterval(function () { goTo(cur + 1); }, 4500); }
    function resetAuto() { clearInterval(auto); startAuto(); }
    track.parentElement.addEventListener('mouseenter', function () { clearInterval(auto); });
    track.parentElement.addEventListener('mouseleave', startAuto);
    startAuto();
})();
</script>

<?php
$homeWhy = [
    ['title' => t('home.why_1_title'), 'text' => t('home.why_1_text'), 'icon' => '<circle cx="9" cy="8" r="3" stroke="currentColor" stroke-width="1.7"/><path d="M3.5 19a5.5 5.5 0 0111 0M16 6.5a3 3 0 010 5M21 19a5.2 5.2 0 00-3.4-4.9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>'],
    ['title' => t('home.why_2_title'), 'text' => t('home.why_2_text'), 'icon' => '<path d="M8 12l2.5 2.5L16 9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 3l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>'],
    ['title' => t('home.why_3_title'), 'text' => t('home.why_3_text'), 'icon' => '<path d="M12 3l7 3v5c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9.2 12l1.9 1.9 3.7-3.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>'],
    ['title' => t('home.why_4_title'), 'text' => t('home.why_4_text'), 'icon' => '<path d="M5 4h14M5 4v16M5 8h9M5 12h6M5 16h11" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>'],
];
?>
<section class="section home-why-section">
    <div class="container">
        <p class="home-kicker home-why-kicker" data-aos="fade-up"><?= e(t('home.why_kicker')) ?></p>
        <div class="home-why-layout">
            <div class="home-why-grid">
                <?php foreach ($homeWhy as $wi => $why): ?>
                    <article class="home-why-item" data-aos="fade-up" data-aos-delay="<?= e((string) ($wi * 100)) ?>">
                        <span class="home-why-index"><?= e(sprintf('%02d', $wi + 1)) ?></span>
                        <span class="home-why-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none"><?= $why['icon'] ?></svg>
                        </span>
                        <h3><?= e($why['title']) ?></h3>
                        <p><?= e($why['text']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="home-why-visual" data-aos="fade-left" data-aos-delay="120" aria-hidden="true">
                <svg viewBox="0 0 200 200" fill="none">
                    <path d="M55 30c18-8 40-6 55 8 14 13 16 30 8 47-6 13-4 22 4 33 6 8 2 20-9 24-16 6-38 4-55-6-19-11-31-31-32-53-1-25 8-45 29-53z" fill="url(#mapGrad)" opacity="0.92"/>
                    <defs>
                        <linearGradient id="mapGrad" x1="30" y1="20" x2="170" y2="180" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#1f8f46"/>
                            <stop offset="0.5" stop-color="#3fae5f"/>
                            <stop offset="1" stop-color="#166b34"/>
                        </linearGradient>
                    </defs>
                </svg>
            </div>
        </div>
    </div>
</section>

<?php if (count($partnerItems) > 0): ?>
<section class="section home-partners-section" data-aos="fade-up">
    <div class="container">
        <p class="home-kicker home-partners-kicker"><?= e(t('home.partners_preview_title')) ?></p>
        <div class="home-partners-grid">
            <?php foreach ($partnerItems as $partner): ?>
                <?php
                $logoPath   = trim((string) ($partner['logo_path'] ?? ''));
                $name       = trim((string) ($partner['name'] ?? ''));
                $websiteUrl = trim((string) ($partner['website_url'] ?? ''));
                $tag        = $websiteUrl !== '' ? 'a' : 'div';
                $attrs      = $websiteUrl !== '' ? ' href="' . e($websiteUrl) . '" target="_blank" rel="noopener noreferrer"' : '';
                ?>
                <<?= $tag ?> class="home-partner-logo-card"<?= $attrs ?>>
                    <?php if ($logoPath !== ''): ?>
                        <img src="<?= e(base_url($logoPath)) ?>" alt="<?= e($name) ?>" loading="lazy" width="140" height="70">
                    <?php else: ?>
                        <span class="home-partner-name-fallback"><?= e($name) ?></span>
                    <?php endif; ?>
                <?= '</' . $tag . '>' ?>
            <?php endforeach; ?>
        </div>
        <div class="home-partners-cta" data-aos="fade-up" data-aos-delay="100">
            <a class="btn btn-secondary" href="<?= e(base_url('partners.php')) ?>">
                <?= e(t('home.see_all_partners')) ?>
            </a>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section home-cta-section">
    <div class="container home-cta-banner" data-aos="fade-up">
        <article class="home-cta-block home-cta-block--partner">
            <span class="home-cta-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 20s-7-4.2-7-9.5A4.3 4.3 0 0112 7a4.3 4.3 0 017 3.5C19 15.8 12 20 12 20z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
            </span>
            <h2><?= e(t('home.cta_partner_title')) ?></h2>
            <p><?= e(t('home.cta_partner_text')) ?></p>
            <a class="btn btn-light" href="<?= e(base_url('partners.php')) ?>"><?= e(t('buttons.become_partner')) ?></a>
        </article>
        <article class="home-cta-block home-cta-block--donate">
            <span class="home-cta-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none"><path d="M12 20s-7-4.2-7-9.5A4.3 4.3 0 0112 7a4.3 4.3 0 017 3.5C19 15.8 12 20 12 20z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
            </span>
            <h2><?= e(t('home.cta_donate_title')) ?></h2>
            <p><?= e(t('home.cta_donate_text')) ?></p>
            <a class="btn btn-light" href="<?= e(base_url('contribute.php')) ?>"><?= e(t('buttons.contribute_now')) ?></a>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
