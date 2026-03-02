<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.about_title');
$pageDescription = t('about.subtitle');

require __DIR__ . '/includes/header.php';
?>

<section class="section about-hero">
    <div class="container about-hero-grid">
        <div class="about-hero-copy" data-aos="fade-right">
            <p class="about-kicker"><?= e(t('site.event_name')) ?></p>
            <h1><?= e(t('about.title')) ?></h1>
            <p class="lead"><?= e(t('about.subtitle')) ?></p>
            <p><?= e(t('site.event_theme')) ?></p>
            <div class="cta-row">
                <a class="btn btn-light" href="<?= e(base_url('registration.php')) ?>"><?= e(t('buttons.register')) ?></a>
                <a class="btn btn-secondary" href="<?= e(base_url('program.php')) ?>"><?= e(t('home.see_full_program')) ?></a>
            </div>
        </div>
        <aside class="about-hero-panel" data-aos="fade-left" data-aos-delay="120">
            <h2><?= e(t('about.objectives_title')) ?></h2>
            <ul class="about-goals-list">
                <li><?= e(t('about.objective_1')) ?></li>
                <li><?= e(t('about.objective_2')) ?></li>
                <li><?= e(t('about.objective_3')) ?></li>
                <li><?= e(t('about.objective_4')) ?></li>
            </ul>
        </aside>
    </div>
</section>

<section class="section about-stats-section">
    <div class="container">
        <div class="stats-strip about-stats-strip">
            <article class="stat-card about-stat-card" data-aos="zoom-in">
                <strong data-counter-end="10" data-counter-suffix="+">0</strong>
                <span><?= e(t('about.stat_days')) ?></span>
            </article>
            <article class="stat-card about-stat-card" data-aos="zoom-in" data-aos-delay="100">
                <strong data-counter-end="4">0</strong>
                <span><?= e(t('about.stat_objectives')) ?></span>
            </article>
            <article class="stat-card about-stat-card" data-aos="zoom-in" data-aos-delay="200">
                <strong data-counter-end="5">0</strong>
                <span><?= e(t('about.stat_roadmap')) ?></span>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container grid-2 about-info-grid">
        <article class="card about-info-card" data-aos="fade-up">
            <h2><?= e(t('about.region_title')) ?></h2>
            <p><?= e(t('about.region_text')) ?></p>
        </article>
        <article class="card about-info-card" data-aos="fade-up" data-aos-delay="120">
            <h2><?= e(t('about.simandou_title')) ?></h2>
            <p><?= e(t('about.simandou_text')) ?></p>
        </article>
    </div>
</section>

<section class="section about-roadmap-section">
    <div class="container">
        <div class="about-section-head" data-aos="fade-up">
            <h2><?= e(t('about.roadmap_title')) ?></h2>
            <p class="lead"><?= e(t('about.roadmap_intro')) ?></p>
        </div>
        <div class="grid-2 about-roadmap-grid">
            <article class="about-roadmap-step" data-aos="fade-up">
                <span class="about-step-index">01</span>
                <p><?= e(t('about.objective_1')) ?></p>
            </article>
            <article class="about-roadmap-step" data-aos="fade-up" data-aos-delay="90">
                <span class="about-step-index">02</span>
                <p><?= e(t('about.objective_2')) ?></p>
            </article>
            <article class="about-roadmap-step" data-aos="fade-up" data-aos-delay="180">
                <span class="about-step-index">03</span>
                <p><?= e(t('about.objective_3')) ?></p>
            </article>
            <article class="about-roadmap-step" data-aos="fade-up" data-aos-delay="270">
                <span class="about-step-index">04</span>
                <p><?= e(t('about.objective_4')) ?></p>
            </article>
        </div>
    </div>
</section>

<section class="section">
    <div class="container grid-2 about-message-grid">
        <article class="card about-message-card" data-aos="fade-right">
            <h2><?= e(t('about.message_title')) ?></h2>
            <p><?= e(t('about.message_text')) ?></p>
        </article>
        <article class="card about-cta-card" data-aos="fade-left" data-aos-delay="120">
            <h2><?= e(t('about.cta_title')) ?></h2>
            <p><?= e(t('about.cta_text')) ?></p>
            <div class="cta-row">
                <a class="btn btn-primary" href="<?= e(base_url('registration.php')) ?>"><?= e(t('buttons.register')) ?></a>
                <a class="btn btn-light" href="<?= e(base_url('contribute.php')) ?>"><?= e(t('buttons.contribute')) ?></a>
            </div>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
