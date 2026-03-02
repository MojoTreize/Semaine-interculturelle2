<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.impressum_title');
$pageDescription = t('impressum.title');

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <article class="card">
            <h1><?= e(t('impressum.title')) ?></h1>
            <p><strong><?= e(t('impressum.publisher')) ?>:</strong> <?= e(t('impressum.publisher_value')) ?></p>
            <p><strong>Adresse:</strong> <?= e(t('impressum.address')) ?></p>
            <p><strong>Email:</strong> <a href="mailto:<?= e(t('impressum.email')) ?>"><?= e(t('impressum.email')) ?></a></p>
            <p><strong><?= e(t('impressum.responsible')) ?>:</strong> <?= e(t('impressum.responsible_value')) ?></p>
            <p><?= e(t('impressum.hosting_note')) ?></p>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
