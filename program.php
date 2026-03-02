<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.program_title');
$pageDescription = t('program.subtitle');

$programItems = fetch_program_items($pdo, current_lang());
$groupedItems = program_by_date($programItems);
$speakers = fetch_featured_speakers($pdo);

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1><?= e(t('program.title')) ?></h1>
        <p class="lead"><?= e(t('program.subtitle')) ?></p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php foreach ($groupedItems as $date => $items): ?>
            <div class="date-block">
                <h2><?= e($date) ?></h2>
                <div class="grid-2">
                    <?php foreach ($items as $item): ?>
                        <?php $typeKey = 'program.' . ((string) ($item['item_type'] ?? 'conference')); ?>
                        <article class="card">
                            <h3><?= e((string) ($item['title'] ?? '')) ?></h3>
                            <div class="meta">
                                <span class="badge"><?= e(substr((string) ($item['start_time'] ?? ''), 0, 5)) ?> - <?= e(substr((string) ($item['end_time'] ?? ''), 0, 5)) ?></span>
                                <span class="badge <?= e((string) ($item['item_type'] ?? 'conference')) ?>"><?= e(t($typeKey)) ?></span>
                            </div>
                            <p><?= e((string) ($item['description'] ?? '')) ?></p>
                            <p class="hint"><?= e(t('program.location')) ?>: <?= e((string) ($item['location'] ?? '')) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2><?= e(t('program.speakers_title')) ?></h2>
        <div class="grid-3">
            <?php foreach ($speakers as $speaker): ?>
                <article class="card">
                    <h3><?= e((string) ($speaker['full_name'] ?? '')) ?></h3>
                    <p class="hint"><?= e((string) ($speaker['title'] ?? '')) ?></p>
                    <p><?= e((string) ($speaker['organization'] ?? '')) ?></p>
                    <p><?= e((string) ($speaker['bio'] ?? '')) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
