<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function api_abort(string $msg, int $code = 400): never
{
    http_response_code($code);
    echo json_encode(['error' => $msg], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    api_abort('Method not allowed', 405);
}

if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    api_abort('Forbidden', 403);
}

$raw   = (string) (file_get_contents('php://input') ?: '');
$input = json_decode($raw, true);

if (!is_array($input)) {
    api_abort('Corps JSON invalide');
}

$paypalOrderId = trim((string) ($input['orderID'] ?? ''));
$donationId    = (int) ($input['donationId'] ?? 0);

if (!preg_match('/^[A-Z0-9\-]{5,50}$/i', $paypalOrderId)) {
    api_abort('Order ID PayPal invalide');
}
if ($donationId <= 0) {
    api_abort('Donation ID invalide');
}

// Vérifie la donation
try {
    $donation = payment_db_fetch_donation($pdo, $donationId);
    if ($donation === null) {
        api_abort('Contribution introuvable.', 404);
    }
    if (($donation['payment_status'] ?? '') === 'paid') {
        http_response_code(200);
        echo json_encode(['success' => true, 'alreadyPaid' => true]);
        exit;
    }
} catch (Throwable) {
    api_abort('Erreur base de données.', 500);
}

// Capture l'ordre PayPal
try {
    $result = paypal_orders_capture($pdo, $paypalOrderId);

    $orderStatus   = strtoupper((string) ($result['status'] ?? ''));
    $capture       = $result['purchase_units'][0]['payments']['captures'][0] ?? [];
    $captureStatus = strtoupper((string) ($capture['status'] ?? ''));

    if ($orderStatus !== 'COMPLETED' || $captureStatus !== 'COMPLETED') {
        api_abort('Paiement non complété par PayPal.', 409);
    }

    payment_db_update_donation($pdo, $donationId, 'paid', $paypalOrderId, true, 'paypal');

    http_response_code(200);
    echo json_encode(['success' => true, 'donationId' => $donationId]);
    exit;
} catch (Throwable $e) {
    payment_db_update_donation($pdo, $donationId, 'failed', null, false, 'paypal');
    api_abort('Capture PayPal échouée : ' . $e->getMessage(), 502);
}
