<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$provider = strtolower((string) ($_GET['provider'] ?? ''));
$message = t('contribute.thanks_pending');
$description = $message;

if ($provider === 'stripe') {
    $sessionId = trim((string) ($_GET['session_id'] ?? ''));
    $session = retrieve_stripe_session($pdo, $sessionId);

    if (is_array($session)) {
        $paid = strtolower((string) ($session['payment_status'] ?? '')) === 'paid';
        $donationId = (int) ($session['metadata']['donation_id'] ?? $session['client_reference_id'] ?? 0);
        if ($donationId <= 0 && $sessionId !== '') {
            $row = payment_db_find_donation_by_provider($pdo, $sessionId, 'stripe');
            $donationId = (int) ($row['id'] ?? 0);
        }

        if ($donationId > 0 && $paid) {
            payment_db_update_donation($pdo, $donationId, 'paid', $sessionId, true, 'stripe');
            $message = t('contribute.thanks_paid');
            $description = $message;
        } elseif ($donationId > 0) {
            payment_db_update_donation($pdo, $donationId, 'pending', $sessionId, false, 'stripe');
            $message = t('contribute.thanks_pending');
            $description = t('contribute.thanks_pending');
        }
    }
}

if ($provider === 'paypal') {
    $donationId = (int) ($_GET['donation_id'] ?? $_GET['custom'] ?? $_GET['cm'] ?? 0);
    $txnId = trim((string) ($_GET['tx'] ?? ''));
    if ($donationId > 0) {
        $row = payment_db_fetch_donation($pdo, $donationId);
        $status = strtolower((string) ($row['payment_status'] ?? 'pending'));
        if ($txnId !== '' && $status !== 'paid') {
            payment_db_update_donation($pdo, $donationId, 'pending', $txnId, false, 'paypal');
            $row = payment_db_fetch_donation($pdo, $donationId);
            $status = strtolower((string) ($row['payment_status'] ?? 'pending'));
        }
        if ($status === 'paid') {
            $message = t('contribute.thanks_paid');
            $description = $message;
        } else {
            $message = t('contribute.thanks_pending');
            $description = 'Paiement PayPal recu. Confirmation en cours.';
        }
    }
}

$pageTitle = t('seo.contribute_title');
$pageDescription = $description;

require __DIR__ . '/includes/header.php';
?>

<section class="section">
    <div class="container">
        <article class="card">
            <h1><?= e(t('contribute.title')) ?></h1>
            <p><?= e($message) ?></p>
            <?php if ($provider === 'paypal'): ?>
                <p class="hint">Le statut final est confirme automatiquement apres verification PayPal (IPN).</p>
            <?php endif; ?>
            <div class="cta-row">
                <a class="btn btn-primary" href="<?= e(base_url('contribute.php')) ?>"><?= e(t('nav.contribute')) ?></a>
                <a class="btn btn-secondary" href="<?= e(base_url('index.php')) ?>"><?= e(t('nav.home')) ?></a>
            </div>
        </article>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
