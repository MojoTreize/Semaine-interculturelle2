<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.program_title');
$pageDescription = t('program.subtitle');

$programItems = fetch_program_items($pdo, current_lang());
$groupedItems = program_by_date($programItems);
$speakers = fetch_featured_speakers($pdo);

$totalSessions = count($programItems);
$totalDays = count($groupedItems);
$totalSpeakers = count($speakers);
$eventTimezone = (string) app_config('app.timezone', 'Europe/Berlin');

$formatProgramDate = static function (string $date, string $lang, string $timezone): string {
    try {
        $dateObj = new DateTimeImmutable($date . ' 00:00:00', new DateTimeZone($timezone));
        return $lang === 'de' ? $dateObj->format('d.m.Y') : $dateObj->format('d/m/Y');
    } catch (Throwable) {
        return $date;
    }
};

require __DIR__ . '/includes/header.php';
?>

<section class="section program-intro-section">
    <div class="container">
        <div class="about-section-head program-intro-shell" data-aos="fade-up">
            <h1><?= e(t('program.title')) ?></h1>
            <p class="lead"><?= e(t('program.subtitle')) ?></p>
        </div>
    </div>
</section>

<section class="section about-stats-section program-stats-section">
    <div class="container">
        <div class="stats-strip about-stats-strip">
            <article class="stat-card about-stat-card" data-aos="zoom-in">
                <strong data-counter-end="<?= e((string) $totalSessions) ?>">0</strong>
                <span><?= e(t('program.stat_sessions')) ?></span>
            </article>
            <article class="stat-card about-stat-card" data-aos="zoom-in" data-aos-delay="100">
                <strong data-counter-end="<?= e((string) $totalDays) ?>">0</strong>
                <span><?= e(t('program.stat_days')) ?></span>
            </article>
            <article class="stat-card about-stat-card" data-aos="zoom-in" data-aos-delay="200">
                <strong data-counter-end="<?= e((string) $totalSpeakers) ?>">0</strong>
                <span><?= e(t('program.stat_speakers')) ?></span>
            </article>
        </div>
    </div>
</section>

<section class="section about-roadmap-section">
    <div class="container">
        <?php $dayIndex = 0; ?>
        <?php foreach ($groupedItems as $date => $items): ?>
            <?php
            $dayIndex++;
            $dayDelay = ($dayIndex % 4) * 60;
            ?>
            <article class="program-date-block card about-message-card" data-aos="fade-up" data-aos-delay="<?= e((string) $dayDelay) ?>">
                <header class="program-date-head">
                    <div>
                        <span class="program-date-chip"><?= e(t('program.day')) ?> <?= e((string) $dayIndex) ?></span>
                        <h2><?= e($formatProgramDate((string) $date, current_lang(), $eventTimezone)) ?></h2>
                    </div>
                    <span class="program-date-count"><?= e((string) count($items)) ?> <?= e(t('program.sessions_short')) ?></span>
                </header>

                <div class="grid-2 about-info-grid program-schedule-grid">
                    <?php foreach ($items as $itemIndex => $item): ?>
                        <?php
                        $itemType = preg_replace('/[^a-z0-9_-]/i', '', strtolower((string) ($item['item_type'] ?? 'conference'))) ?: 'conference';
                        $typeKey = 'program.' . $itemType;
                        $aosDelay = ($itemIndex % 4) * 80;
                        ?>
                        <?php
                        $startTime    = substr((string) ($item['start_time']   ?? ''), 0, 5);
                        $endTime      = substr((string) ($item['end_time']     ?? ''), 0, 5);
                        $description  = trim((string) ($item['description']  ?? ''));
                        $location     = trim((string) ($item['location']     ?? ''));
                        $speakersRaw  = trim((string) ($item['speakers_list'] ?? ''));

                        // Parse speakers list (one per line, ignore blank lines)
                        $speakersList = $speakersRaw !== ''
                            ? array_values(array_filter(array_map('trim', explode("\n", $speakersRaw))))
                            : [];

                        // Dynamic label depending on session type
                        $speakersLabel = match ($itemType) {
                            'panel'       => current_lang() === 'de' ? 'Diskussionsteilnehmer' : 'Panélistes',
                            'conference'  => current_lang() === 'de' ? 'Referenten'            : 'Intervenants',
                            'workshop'    => current_lang() === 'de' ? 'Moderatoren'           : 'Animateurs',
                            'ceremony'    => current_lang() === 'de' ? 'Beteiligte'            : 'Officiels',
                            'networking'  => current_lang() === 'de' ? 'Organisatoren'         : 'Organisateurs',
                            'exhibition'  => current_lang() === 'de' ? 'Aussteller'            : 'Exposants',
                            default       => current_lang() === 'de' ? 'Beteiligte'            : 'Intervenants',
                        };
                        ?>
                        <article class="card about-info-card program-schedule-card" data-aos="fade-up" data-aos-delay="<?= e((string) $aosDelay) ?>">
                            <h3><?= e((string) ($item['title'] ?? '')) ?></h3>
                            <div class="meta program-schedule-meta">
                                <?php if ($startTime !== '' || $endTime !== ''): ?>
                                    <span class="badge">
                                        <?= e($startTime) ?>
                                        <?= ($startTime !== '' && $endTime !== '') ? ' - ' . e($endTime) : ($endTime !== '' ? e($endTime) : '') ?>
                                    </span>
                                <?php endif; ?>
                                <span class="badge <?= e($itemType) ?>"><?= e(t($typeKey)) ?></span>
                            </div>
                            <?php if ($description !== ''): ?>
                                <p><?= nl2br(e($description)) ?></p>
                            <?php endif; ?>
                            <?php if ($speakersList !== []): ?>
                                <div class="program-speakers-list">
                                    <p class="program-speakers-label"><?= e($speakersLabel) ?></p>
                                    <ul>
                                        <?php foreach ($speakersList as $speaker): ?>
                                            <li><?= e($speaker) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ($location !== ''): ?>
                                <p class="hint">
                                    📍 <?= e(t('program.location')) ?> :
                                    <a class="program-location-link" href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode($location) ?>" target="_blank" rel="noopener noreferrer"><?= e($location) ?></a>
                                </p>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="section program-speakers-section">
    <div class="container">
        <div class="about-section-head" data-aos="fade-up">
            <h2><?= e(t('program.speakers_title')) ?></h2>
            <p class="lead"><?= e(t('program.speakers_intro')) ?></p>
        </div>

        <div class="grid-3 program-speakers-grid">
            <?php foreach ($speakers as $speakerIndex => $speaker): ?>
                <?php
                $speakerDelay = ($speakerIndex % 3) * 90;
                $photoPath    = trim((string) ($speaker['photo_path'] ?? ''));
                $hasPhoto     = $photoPath !== '' && file_exists(__DIR__ . '/' . ltrim($photoPath, '/'));
                $initials     = implode('', array_map(static fn($w) => mb_strtoupper(mb_substr($w, 0, 1)), array_slice(explode(' ', (string) ($speaker['full_name'] ?? '')), 0, 2)));
                ?>
                <article class="card program-speaker-card" data-aos="fade-up" data-aos-delay="<?= e((string) $speakerDelay) ?>">
                    <div class="speaker-photo-wrap">
                        <?php if ($hasPhoto): ?>
                            <img class="speaker-photo" src="<?= e($photoPath) ?>" alt="<?= e((string) ($speaker['full_name'] ?? '')) ?>" loading="lazy">
                        <?php else: ?>
                            <div class="speaker-avatar" aria-hidden="true"><?= e($initials) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="speaker-info">
                        <h3><?= e((string) ($speaker['full_name'] ?? '')) ?></h3>
                        <?php if (trim((string) ($speaker['title'] ?? '')) !== ''): ?>
                            <p class="hint"><?= e((string) $speaker['title']) ?></p>
                        <?php endif; ?>
                        <?php if (trim((string) ($speaker['organization'] ?? '')) !== ''): ?>
                            <p class="speaker-org"><?= e((string) $speaker['organization']) ?></p>
                        <?php endif; ?>
                        <?php if (trim((string) ($speaker['bio'] ?? '')) !== ''): ?>
                            <p class="speaker-bio"><?= nl2br(e((string) $speaker['bio'])) ?></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
