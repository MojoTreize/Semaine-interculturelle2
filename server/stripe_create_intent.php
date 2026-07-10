<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Exception\ApiErrorException;

require_http_method('POST');

try {
    $orderId = create_pending_order('stripe', ORDER_AMOUNT_CENTS, ORDER_CURRENCY);

    Stripe::setApiKey(STRIPE_SECRET_KEY);
    $intent = PaymentIntent::create([
        'amount' => ORDER_AMOUNT_CENTS,
        'currency' => strtolower(ORDER_CURRENCY),
        'metadata' => [
            'order_id' => (string) $orderId,
        ],
        'automatic_payment_methods' => [
            'enabled' => true,
        ],
    ]);

    if (empty($intent->client_secret)) {
        throw new RuntimeException('Unable to create Stripe PaymentIntent.');
    }

    update_order_provider_ref($orderId, (string) $intent->id);

    json_response([
        'clientSecret' => (string) $intent->client_secret,
        'orderId' => $orderId,
        'amount' => ORDER_AMOUNT_CENTS,
        'currency' => ORDER_CURRENCY,
    ]);
} catch (ApiErrorException $e) {
    json_response(['error' => 'Stripe API error.'], 502);
} catch (Throwable $e) {
    json_response(['error' => 'Internal server error.'], 500);
}
