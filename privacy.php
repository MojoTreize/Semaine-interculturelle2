<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$pageTitle = t('seo.privacy_title');
$pageDescription = t('privacy.title');

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <article class="card">
            <h1><?= e(t('privacy.title')) ?></h1>
            <p><?= e(t('privacy.intro')) ?></p>
            <p><?= e(t('privacy.legal_basis')) ?></p>
            <p><?= e(t('privacy.rights')) ?></p>
            <p><?= e(t('privacy.security_note')) ?></p>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
