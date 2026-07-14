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

$amount = isset($input['amount']) ? (float) $input['amount'] : 0.0;

if (!is_finite($amount) || $amount < 1.0 || $amount > 10000.0) {
    api_abort('Montant invalide. Min 1 €, max 10 000 €.');
}

// Enregistre la donation en attente
try {
    $stmt = $pdo->prepare(
        'INSERT INTO donations (amount, currency, motive, payment_method, payment_status, language, is_public)
         VALUES (:amount, :currency, :motive, :method, :status, :lang, 1)'
    );
    $stmt->execute([
        'amount'   => $amount,
        'currency' => payment_currency($pdo),
        'motive'   => 'general',
        'method'   => 'paypal',
        'status'   => 'pending',
        'lang'     => current_lang(),
    ]);
    $donationId = (int) $pdo->lastInsertId();
} catch (Throwable) {
    api_abort('Erreur base de données.', 500);
}

// Crée l'ordre PayPal via Orders API v2
try {
    $paypalOrderId = paypal_orders_create($pdo, $donationId, $amount);
    payment_db_update_donation($pdo, $donationId, 'pending', $paypalOrderId, false, 'paypal');

    http_response_code(200);
    echo json_encode(['id' => $paypalOrderId, 'donationId' => $donationId], JSON_UNESCAPED_UNICODE);
    exit;
} catch (Throwable $e) {
    payment_db_update_donation($pdo, $donationId, 'failed', null, false, 'paypal');
    api_abort('Erreur PayPal : ' . $e->getMessage(), 502);
}
