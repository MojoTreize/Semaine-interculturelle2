<?php

declare(strict_types=1);

if (!function_exists('payment_currency')) {
    function payment_currency(mixed $pdo): string
    {
        $configured = strtoupper((string) app_config('payment.currency', 'EUR'));
        $fromSetting = strtoupper(get_setting($pdo, 'currency', $configured));

        return $fromSetting !== '' ? $fromSetting : 'EUR';
    }
}

if (!function_exists('stripe_secret_key')) {
    function stripe_secret_key(mixed $pdo): string
    {
        $fromDb = get_setting($pdo, 'stripe_secret_key', '');
        if ($fromDb !== '') {
            return $fromDb;
        }

        return (string) app_config('payment.stripe_secret_key', '');
    }
}

if (!function_exists('stripe_public_key')) {
    function stripe_public_key(mixed $pdo): string
    {
        $fromDb = get_setting($pdo, 'stripe_public_key', '');
        if ($fromDb !== '') {
            return $fromDb;
        }

        return (string) app_config('payment.stripe_public_key', '');
    }
}

if (!function_exists('create_stripe_checkout_session')) {
    function create_stripe_checkout_session(mixed $pdo, int $donationId, float $amount, string $description): array
    {
        $secret = stripe_secret_key($pdo);
        if ($secret === '') {
            return ['ok' => false, 'error' => 'Stripe key is missing.'];
        }
        if (!function_exists('curl_init')) {
            return ['ok' => false, 'error' => 'cURL extension is required.'];
        }

        $currency = strtolower(payment_currency($pdo));
        $unitAmount = (int) round($amount * 100);

        $payload = [
            'mode' => 'payment',
            'success_url' => base_url('payment_success.php?provider=stripe&session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => base_url('payment_cancel.php?provider=stripe&donation_id=' . $donationId),
            'client_reference_id' => (string) $donationId,
            'metadata[donation_id]' => (string) $donationId,
            'line_items[0][quantity]' => 1,
            'line_items[0][price_data][currency]' => $currency,
            'line_items[0][price_data][unit_amount]' => $unitAmount,
            'line_items[0][price_data][product_data][name]' => $description,
        ];

        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $secret,
            ],
            CURLOPT_POSTFIELDS => http_build_query($payload),
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || $curlError !== '') {
            return ['ok' => false, 'error' => $curlError !== '' ? $curlError : 'Stripe request failed'];
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 400 || !is_array($decoded) || empty($decoded['id']) || empty($decoded['url'])) {
            $error = is_array($decoded) && isset($decoded['error']['message']) ? (string) $decoded['error']['message'] : 'Unable to create Stripe session';
            return ['ok' => false, 'error' => $error];
        }

        return [
            'ok' => true,
            'session_id' => (string) $decoded['id'],
            'checkout_url' => (string) $decoded['url'],
        ];
    }
}

if (!function_exists('retrieve_stripe_session')) {
    function retrieve_stripe_session(mixed $pdo, string $sessionId): ?array
    {
        $secret = stripe_secret_key($pdo);
        if ($secret === '' || $sessionId === '') {
            return null;
        }
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . rawurlencode($sessionId));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $secret,
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            return null;
        }

        $decoded = json_decode($response, true);
        return is_array($decoded) ? $decoded : null;
    }
}

if (!function_exists('paypal_business_email')) {
    function paypal_business_email(mixed $pdo): string
    {
        $fromDb = get_setting($pdo, 'paypal_business_email', '');
        if ($fromDb !== '') {
            return $fromDb;
        }

        return (string) app_config('payment.paypal_business_email', '');
    }
}

if (!function_exists('paypal_checkout_url')) {
    function paypal_checkout_url(mixed $pdo, int $donationId, float $amount, string $itemLabel): string
    {
        $business = paypal_business_email($pdo);
        if ($business === '') {
            return '';
        }

        $mode = strtolower(get_setting($pdo, 'paypal_mode', (string) app_config('payment.paypal_mode', 'sandbox')));
        $endpoint = $mode === 'live'
            ? 'https://www.paypal.com/cgi-bin/webscr'
            : 'https://www.sandbox.paypal.com/cgi-bin/webscr';

        $params = [
            'cmd' => '_xclick',
            'business' => $business,
            'item_name' => $itemLabel,
            'amount' => number_format($amount, 2, '.', ''),
            'currency_code' => payment_currency($pdo),
            'return' => base_url('payment_success.php?provider=paypal&donation_id=' . $donationId),
            'cancel_return' => base_url('payment_cancel.php?provider=paypal&donation_id=' . $donationId),
            'custom' => (string) $donationId,
            'rm' => '2',
        ];

        return $endpoint . '?' . http_build_query($params);
    }
}
