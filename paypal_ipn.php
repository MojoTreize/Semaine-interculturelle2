<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

$rawPayload = (string) file_get_contents('php://input');
if ($rawPayload === '') {
    http_response_code(400);
    exit('Empty payload');
}

$mode = paypal_mode($pdo);
if (!paypal_verify_ipn($rawPayload, $mode)) {
    http_response_code(400);
    exit('Invalid IPN');
}

$ipn = [];
parse_str($rawPayload, $ipn);

$donationId = (int) ($ipn['custom'] ?? 0);
$paymentStatus = strtoupper(trim((string) ($ipn['payment_status'] ?? '')));
$txnId = trim((string) ($ipn['txn_id'] ?? ''));
$receiverEmail = strtolower(trim((string) ($ipn['receiver_email'] ?? '')));
$businessEmail = strtolower(trim(paypal_business_email($pdo)));
$gross = (float) ($ipn['mc_gross'] ?? 0);
$currency = strtoupper(trim((string) ($ipn['mc_currency'] ?? '')));

if ($businessEmail === '') {
    http_response_code(400);
    exit('PayPal business email not configured');
}

if ($donationId > 0) {
    $donation = payment_db_fetch_donation($pdo, $donationId);

    if (is_array($donation) && $receiverEmail === $businessEmail) {
        $amountMatches = abs(((float) ($donation['amount'] ?? 0)) - $gross) < 0.01;
        $currencyMatches = strtoupper((string) ($donation['currency'] ?? '')) === $currency;

        if ($paymentStatus === 'COMPLETED' && $amountMatches && $currencyMatches) {
            payment_db_update_donation($pdo, $donationId, 'paid', $txnId !== '' ? $txnId : null, true, 'paypal');
        } elseif ($paymentStatus === 'PENDING') {
            payment_db_update_donation($pdo, $donationId, 'pending', $txnId !== '' ? $txnId : null, false, 'paypal');
        } elseif (in_array($paymentStatus, ['FAILED', 'DENIED', 'EXPIRED', 'VOIDED'], true)) {
            payment_db_update_donation($pdo, $donationId, 'failed', $txnId !== '' ? $txnId : null, false, 'paypal');
        } elseif (in_array($paymentStatus, ['REFUNDED', 'REVERSED'], true)) {
            payment_db_update_donation($pdo, $donationId, 'canceled', $txnId !== '' ? $txnId : null, false, 'paypal');
        }
    }
}

http_response_code(200);
echo 'OK';
