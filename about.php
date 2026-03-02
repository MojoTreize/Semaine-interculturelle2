<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.about_title');
$pageDescription = t('about.subtitle');

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <h1><?= e(t('about.title')) ?></h1>
        <p class="lead"><?= e(t('about.subtitle')) ?></p>
    </div>
</section>

<section class="section">
    <div class="container grid-2">
        <article class="card">
            <h2><?= e(t('about.region_title')) ?></h2>
            <p><?= e(t('about.region_text')) ?></p>
        </article>
        <article class="card">
            <h2><?= e(t('about.objectives_title')) ?></h2>
            <ul>
                <li><?= e(t('about.objective_1')) ?></li>
                <li><?= e(t('about.objective_2')) ?></li>
                <li><?= e(t('about.objective_3')) ?></li>
                <li><?= e(t('about.objective_4')) ?></li>
            </ul>
        </article>
    </div>
</section>

<section class="section">
    <div class="container grid-2">
        <article class="card">
            <h2><?= e(t('about.simandou_title')) ?></h2>
            <p><?= e(t('about.simandou_text')) ?></p>
        </article>
        <article class="card">
            <h2><?= e(t('about.message_title')) ?></h2>
            <p><?= e(t('about.message_text')) ?></p>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
