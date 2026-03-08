<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method Not Allowed');
}

$payload = (string) file_get_contents('php://input');
$signature = (string) ($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');
$secret = stripe_webhook_secret($pdo);

if ($secret === '' || !stripe_verify_webhook_signature($payload, $signature, $secret)) {
    http_response_code(400);
    exit('Invalid signature');
}

$event = json_decode($payload, true);
if (!is_array($event)) {
    http_response_code(400);
    exit('Invalid payload');
}

$type = (string) ($event['type'] ?? '');
$object = $event['data']['object'] ?? null;

if (!is_array($object)) {
    http_response_code(200);
    exit('OK');
}

if ($type === 'checkout.session.completed') {
    $sessionId = (string) ($object['id'] ?? '');
    $donationId = (int) ($object['metadata']['donation_id'] ?? $object['client_reference_id'] ?? 0);

    if ($donationId <= 0 && $sessionId !== '') {
        $row = payment_db_find_donation_by_provider($pdo, $sessionId, 'stripe');
        $donationId = (int) ($row['id'] ?? 0);
    }

    if ($donationId > 0) {
        payment_db_update_donation($pdo, $donationId, 'paid', $sessionId !== '' ? $sessionId : null, true, 'stripe');
    }
}

if ($type === 'checkout.session.expired') {
    $sessionId = (string) ($object['id'] ?? '');
    $donationId = (int) ($object['metadata']['donation_id'] ?? $object['client_reference_id'] ?? 0);

    if ($donationId <= 0 && $sessionId !== '') {
        $row = payment_db_find_donation_by_provider($pdo, $sessionId, 'stripe');
        $donationId = (int) ($row['id'] ?? 0);
    }

    if ($donationId > 0) {
        payment_db_update_donation($pdo, $donationId, 'canceled', $sessionId !== '' ? $sessionId : null, false, 'stripe');
    }
}

http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['received' => true], JSON_UNESCAPED_SLASHES);
