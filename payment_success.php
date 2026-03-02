<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$provider = strtolower((string) ($_GET['provider'] ?? ''));
$message = in_array($provider, ['stripe', 'paypal'], true)
    ? t('contribute.thanks_paid')
    : t('contribute.thanks_pending');

$pageTitle = t('seo.contribute_title');
$pageDescription = $message;

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <article class="card">
            <h1><?= e(t('contribute.title')) ?></h1>
            <p><?= e($message) ?></p>
            <div class="cta-row">
                <a class="btn btn-primary" href="<?= e(base_url('contribute.php')) ?>"><?= e(t('nav.contribute')) ?></a>
                <a class="btn btn-secondary" href="<?= e(base_url('index.php')) ?>"><?= e(t('nav.home')) ?></a>
            </div>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
