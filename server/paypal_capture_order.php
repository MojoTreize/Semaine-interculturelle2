<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

require_http_method('POST');

try {
    $input = read_json_body();
    $paypalOrderId = $input['orderID'] ?? '';

    if (!is_string($paypalOrderId) || !preg_match('/^[A-Z0-9-]+$/', $paypalOrderId)) {
        json_response(['error' => 'Invalid PayPal order ID.'], 422);
    }

    $order = find_order_by_provider_ref('paypal', $paypalOrderId);
    if ($order === null) {
        json_response(['error' => 'Order not found.'], 404);
    }

    if (($order['status'] ?? '') === 'paid') {
        json_response([
            'success' => true,
            'orderId' => (int) $order['id'],
            'alreadyPaid' => true,
        ]);
    }

    $accessToken = paypal_access_token();
    $response = paypal_api_request(
        'POST',
        '/v2/checkout/orders/' . rawurlencode($paypalOrderId) . '/capture',
        $accessToken
    );

    $statusCode = (int) ($response['status'] ?? 500);
    $body = is_array($response['body'] ?? null) ? $response['body'] : [];

    if ($statusCode < 200 || $statusCode >= 300) {
        json_response(['error' => 'PayPal capture failed.'], 502);
    }

    $orderStatus = strtoupper((string) ($body['status'] ?? ''));
    $capture = $body['purchase_units'][0]['payments']['captures'][0] ?? [];
    $captureStatus = strtoupper((string) ($capture['status'] ?? ''));
    $captureValue = (string) ($capture['amount']['value'] ?? '');
    $captureCurrency = strtoupper((string) ($capture['amount']['currency_code'] ?? ''));

    if ($orderStatus !== 'COMPLETED' || $captureStatus !== 'COMPLETED') {
        json_response(['error' => 'PayPal payment not completed.'], 409);
    }

    $capturedAmountCents = decimal_string_to_cents($captureValue);
    $expectedAmount = (int) ($order['amount'] ?? 0);
    $expectedCurrency = strtoupper((string) ($order['currency'] ?? ''));

    if ($capturedAmountCents !== $expectedAmount || $captureCurrency !== $expectedCurrency) {
        json_response(['error' => 'Amount or currency mismatch.'], 400);
    }

    mark_order_paid((int) $order['id']);

    json_response([
        'success' => true,
        'orderId' => (int) $order['id'],
    ]);
} catch (InvalidArgumentException $e) {
    json_response(['error' => 'Invalid request body.'], 422);
} catch (Throwable $e) {
    json_response(['error' => 'Internal server error.'], 500);
}
