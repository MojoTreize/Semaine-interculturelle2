<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$provider = strtolower((string) ($_GET['provider'] ?? ''));
$message = t('contribute.thanks_pending');

if ($provider === 'stripe') {
    $sessionId = (string) ($_GET['session_id'] ?? '');
    $session = retrieve_stripe_session($pdo, $sessionId);

    if (is_array($session) && (($session['payment_status'] ?? '') === 'paid')) {
        $donationId = (int) (($session['metadata']['donation_id'] ?? $session['client_reference_id'] ?? 0));
        if ($donationId > 0) {
            $checkStmt = $pdo->prepare('SELECT donor_email, donor_name, payment_status FROM donations WHERE id = :id LIMIT 1');
            $checkStmt->execute(['id' => $donationId]);
            $donation = $checkStmt->fetch();

            $update = $pdo->prepare('UPDATE donations SET payment_status = :status, payment_provider_id = :provider_id, paid_at = ' . db_now_expression($pdo) . ' WHERE id = :id');
            $update->execute([
                'status' => 'paid',
                'provider_id' => $sessionId,
                'id' => $donationId,
            ]);

            if ($donation && $donation['payment_status'] !== 'paid' && !empty($donation['donor_email'])) {
                $subject = t('emails.donation_subject');
                $body = '<p>' . e(t('contribute.thanks_paid')) . '</p>';
                send_email((string) $donation['donor_email'], (string) ($donation['donor_name'] ?? ''), $subject, $body, strip_tags($body));
            }
        }
        $message = t('contribute.thanks_paid');
    }
}

if ($provider === 'paypal') {
    $donationId = (int) ($_GET['donation_id'] ?? ($_GET['custom'] ?? 0));
    $statusText = strtolower((string) ($_GET['st'] ?? 'completed'));
    $txn = (string) ($_GET['tx'] ?? '');

    if ($donationId > 0) {
        $newStatus = str_contains($statusText, 'completed') ? 'paid' : 'pending';
        if ($newStatus === 'paid') {
            $update = $pdo->prepare('UPDATE donations SET payment_status = :status, payment_provider_id = :provider_id, paid_at = ' . db_now_expression($pdo) . ' WHERE id = :id');
        } else {
            $update = $pdo->prepare('UPDATE donations SET payment_status = :status, payment_provider_id = :provider_id WHERE id = :id');
        }
        $update->execute([
            'status' => $newStatus,
            'provider_id' => $txn !== '' ? $txn : 'paypal-' . $donationId,
            'id' => $donationId,
        ]);

        $checkStmt = $pdo->prepare('SELECT donor_email, donor_name FROM donations WHERE id = :id LIMIT 1');
        $checkStmt->execute(['id' => $donationId]);
        $donation = $checkStmt->fetch();

        if ($newStatus === 'paid' && $donation && !empty($donation['donor_email'])) {
            $subject = t('emails.donation_subject');
            $body = '<p>' . e(t('contribute.thanks_paid')) . '</p>';
            send_email((string) $donation['donor_email'], (string) ($donation['donor_name'] ?? ''), $subject, $body, strip_tags($body));
        }

        $message = $newStatus === 'paid' ? t('contribute.thanks_paid') : t('contribute.thanks_pending');
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
