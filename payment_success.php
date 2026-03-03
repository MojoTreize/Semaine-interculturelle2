<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$provider = strtolower((string) ($_GET['provider'] ?? ''));
$message = t('contribute.thanks_pending');

if ($pdo instanceof PDO) {
    try {
        if ($provider === 'stripe') {
            $sessionId = trim((string) ($_GET['session_id'] ?? ''));
            $session = $sessionId !== '' ? retrieve_stripe_session($pdo, $sessionId) : null;
            $paymentStatus = strtolower((string) ($session['payment_status'] ?? ''));
            $donationId = (int) ($session['metadata']['donation_id'] ?? $session['client_reference_id'] ?? 0);

            if ($paymentStatus === 'paid' && $donationId > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE donations
                     SET payment_status = :payment_status, paid_at = ' . db_now_expression($pdo) . ', payment_provider_id = :provider_id
                     WHERE id = :id'
                );
                $stmt->execute([
                    'payment_status' => 'paid',
                    'provider_id' => $sessionId,
                    'id' => $donationId,
                ]);
                $message = t('contribute.thanks_paid');
            }
        }

        if ($provider === 'paypal') {
            $donationId = (int) ($_GET['donation_id'] ?? 0);
            if ($donationId > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE donations
                     SET payment_status = :payment_status, paid_at = ' . db_now_expression($pdo) . '
                     WHERE id = :id'
                );
                $stmt->execute([
                    'payment_status' => 'paid',
                    'id' => $donationId,
                ]);
                $message = t('contribute.thanks_paid');
            }
        }
    } catch (Throwable) {
        $message = t('contribute.thanks_pending');
    }
}

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
