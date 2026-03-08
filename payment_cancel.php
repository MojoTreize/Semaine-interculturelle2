<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$donationId = (int) ($_GET['donation_id'] ?? 0);
$provider = strtolower((string) ($_GET['provider'] ?? ''));

if ($donationId > 0 && in_array($provider, ['stripe', 'paypal'], true)) {
    $donation = payment_db_fetch_donation($pdo, $donationId);
    $currentStatus = strtolower((string) ($donation['payment_status'] ?? ''));
    if ($currentStatus !== 'paid') {
        payment_db_update_donation($pdo, $donationId, 'canceled', null, false, $provider);
    }
}

set_flash('warning', t('contribute.payment_cancelled'));
redirect('contribute.php');
