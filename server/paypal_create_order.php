<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

require_http_method('POST');

try {
    $orderId = create_pending_order('paypal', ORDER_AMOUNT_CENTS, ORDER_CURRENCY);
    $accessToken = paypal_access_token();

    $requestBody = [
        'intent' => 'CAPTURE',
        'purchase_units' => [
            [
                'reference_id' => (string) $orderId,
                'custom_id' => (string) $orderId,
                'amount' => [
                    'currency_code' => ORDER_CURRENCY,
                    'value' => cents_to_decimal_string(ORDER_AMOUNT_CENTS),
                ],
            ],
        ],
        'application_context' => [
            'return_url' => app_base_url() . '/public/success.php?provider=paypal',
            'cancel_url' => app_base_url() . '/public/cancel.php?provider=paypal',
            'user_action' => 'PAY_NOW',
        ],
    ];

    $response = paypal_api_request('POST', '/v2/checkout/orders', $accessToken, $requestBody);
    if (($response['status'] ?? 500) < 200 || ($response['status'] ?? 500) >= 300 || empty($response['body']['id'])) {
        throw new RuntimeException('PayPal order creation failed.');
    }

    $paypalOrderId = (string) $response['body']['id'];
    update_order_provider_ref($orderId, $paypalOrderId);

    // PayPal Smart Buttons expects { id: "PAYPAL_ORDER_ID" }.
    json_response(['id' => $paypalOrderId]);
} catch (Throwable $e) {
    json_response(['error' => 'Unable to create PayPal order.'], 500);
}
