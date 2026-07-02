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

// Enregistre la donation
try {
    $stmt = $pdo->prepare(
        'INSERT INTO donations (amount, currency, motive, payment_method, payment_status, language, is_public)
         VALUES (:amount, :currency, :motive, :method, :status, :lang, 1)'
    );
    $stmt->execute([
        'amount'   => $amount,
        'currency' => payment_currency($pdo),
        'motive'   => 'general',
        'method'   => 'stripe',
        'status'   => 'pending',
        'lang'     => current_lang(),
    ]);
    $donationId = (int) $pdo->lastInsertId();
} catch (Throwable) {
    api_abort('Erreur base de données.', 500);
}

// Crée la session Stripe Checkout
$description = t('site.short_name') . ' - Don #' . $donationId;
$session     = create_stripe_checkout_session($pdo, $donationId, $amount, $description);

if (empty($session['ok']) || empty($session['checkout_url'])) {
    payment_db_update_donation($pdo, $donationId, 'failed', null, false, 'stripe');
    api_abort($session['error'] ?? 'Stripe indisponible.', 502);
}

payment_db_update_donation($pdo, $donationId, 'pending', $session['session_id'] ?? null, false, 'stripe');

http_response_code(200);
echo json_encode(['url' => $session['checkout_url']]);
exit;
