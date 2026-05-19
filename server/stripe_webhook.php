<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

require_http_method('POST');

if (STRIPE_WEBHOOK_SECRET === '') {
    json_response(['error' => 'Missing STRIPE_WEBHOOK_SECRET.'], 500);
}

$payload = file_get_contents('php://input');
if ($payload === false) {
    json_response(['error' => 'Cannot read webhook payload.'], 400);
}

$signatureHeader = (string) ($_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '');

try {
    $event = Webhook::constructEvent($payload, $signatureHeader, STRIPE_WEBHOOK_SECRET);
} catch (UnexpectedValueException | SignatureVerificationException $e) {
    json_response(['error' => 'Invalid webhook signature.'], 400);
}

if (($event->type ?? '') === 'payment_intent.succeeded') {
    $intent = $event->data->object;
    $providerRef = (string) ($intent->id ?? '');
    $metadataOrderId = isset($intent->metadata->order_id) ? (int) $intent->metadata->order_id : 0;
    $amountReceived = (int) ($intent->amount_received ?? 0);
    $currency = strtoupper((string) ($intent->currency ?? ''));

    $order = null;
    if ($metadataOrderId > 0) {
        $order = find_order_by_id($metadataOrderId);
    }
    if ($order === null && $providerRef !== '') {
        $order = find_order_by_provider_ref('stripe', $providerRef);
    }

    if (is_array($order)) {
        $expectedAmount = (int) ($order['amount'] ?? 0);
        $expectedCurrency = strtoupper((string) ($order['currency'] ?? ''));

        if ($amountReceived === $expectedAmount && $currency === $expectedCurrency) {
            mark_order_paid((int) $order['id']);
        }
    }
}

json_response(['received' => true]);
