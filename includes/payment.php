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

if (!function_exists('paypal_mode')) {
    function paypal_mode(mixed $pdo): string
    {
        $mode = strtolower(get_setting($pdo, 'paypal_mode', (string) app_config('payment.paypal_mode', 'sandbox')));
        return $mode === 'live' ? 'live' : 'sandbox';
    }
}

if (!function_exists('payment_db_fetch_donation')) {
    function payment_db_fetch_donation(mixed $pdo, int $donationId): ?array
    {
        if ($donationId <= 0 || !is_object($pdo) || !method_exists($pdo, 'prepare')) {
            return null;
        }

        try {
            $stmt = $pdo->prepare('SELECT id, amount, currency, payment_method, payment_status, payment_provider_id
                                   FROM donations WHERE id = :id LIMIT 1');
            $stmt->execute(['id' => $donationId]);
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (Throwable) {
            return null;
        }
    }
}

if (!function_exists('payment_db_find_donation_by_provider')) {
    function payment_db_find_donation_by_provider(mixed $pdo, string $providerId, string $method = ''): ?array
    {
        if ($providerId === '' || !is_object($pdo) || !method_exists($pdo, 'prepare')) {
            return null;
        }

        try {
            $sql = 'SELECT id, amount, currency, payment_method, payment_status, payment_provider_id
                    FROM donations
                    WHERE payment_provider_id = :provider_id';
            $params = ['provider_id' => $providerId];

            if ($method !== '') {
                $sql .= ' AND payment_method = :payment_method';
                $params['payment_method'] = $method;
            }

            $sql .= ' ORDER BY id DESC LIMIT 1';

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $row = $stmt->fetch();
            return is_array($row) ? $row : null;
        } catch (Throwable) {
            return null;
        }
    }
}

if (!function_exists('payment_db_update_donation')) {
    function payment_db_update_donation(
        mixed $pdo,
        int $donationId,
        string $status,
        ?string $providerId = null,
        bool $setPaidAt = false,
        string $methodGuard = ''
    ): bool {
        $allowed = ['pending', 'paid', 'failed', 'canceled'];
        if ($donationId <= 0 || !in_array($status, $allowed, true)) {
            return false;
        }
        if (!is_object($pdo) || !method_exists($pdo, 'prepare')) {
            return false;
        }

        try {
            $parts = ['payment_status = :status'];
            $params = [
                'id' => $donationId,
                'status' => $status,
            ];

            if ($providerId !== null && $providerId !== '') {
                $parts[] = 'payment_provider_id = :provider_id';
                $params['provider_id'] = $providerId;
            }

            if ($setPaidAt) {
                $parts[] = 'paid_at = ' . db_now_expression($pdo);
            }

            $sql = 'UPDATE donations SET ' . implode(', ', $parts) . ' WHERE id = :id';
            if ($methodGuard !== '') {
                $sql .= ' AND payment_method = :method_guard';
                $params['method_guard'] = $methodGuard;
            }

            $stmt = $pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Throwable) {
            return false;
        }
    }
}

if (!function_exists('stripe_webhook_secret')) {
    function stripe_webhook_secret(mixed $pdo): string
    {
        $fromDb = get_setting($pdo, 'stripe_webhook_secret', '');
        if ($fromDb !== '') {
            return $fromDb;
        }

        return (string) app_config('payment.stripe_webhook_secret', '');
    }
}

if (!function_exists('stripe_verify_webhook_signature')) {
    function stripe_verify_webhook_signature(string $payload, string $signatureHeader, string $secret, int $tolerance = 300): bool
    {
        if ($payload === '' || $signatureHeader === '' || $secret === '') {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $chunk) {
            $pair = explode('=', trim($chunk), 2);
            if (count($pair) === 2) {
                $parts[$pair[0]][] = $pair[1];
            }
        }

        $timestamp = isset($parts['t'][0]) ? (int) $parts['t'][0] : 0;
        $signatures = $parts['v1'] ?? [];

        if ($timestamp <= 0 || empty($signatures)) {
            return false;
        }

        if (abs(time() - $timestamp) > $tolerance) {
            return false;
        }

        $signedPayload = $timestamp . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, $secret);

        foreach ($signatures as $sig) {
            if (hash_equals($expected, $sig)) {
                return true;
            }
        }

        return false;
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

if (!function_exists('paypal_verify_ipn')) {
    function paypal_verify_ipn(string $rawPayload, string $mode): bool
    {
        if ($rawPayload === '') {
            return false;
        }
        if (!function_exists('curl_init')) {
            return false;
        }

        $endpoint = $mode === 'live'
            ? 'https://ipnpb.paypal.com/cgi-bin/webscr'
            : 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

        $verifyPayload = 'cmd=_notify-validate&' . $rawPayload;

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Connection: Close',
                'Content-Type: application/x-www-form-urlencoded',
            ],
            CURLOPT_POSTFIELDS => $verifyPayload,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $curlError !== '' || $httpCode >= 400) {
            return false;
        }

        return trim((string) $response) === 'VERIFIED';
    }
}

if (!function_exists('paypal_checkout_url')) {
    function paypal_checkout_url(mixed $pdo, int $donationId, float $amount, string $itemLabel): string
    {
        $business = paypal_business_email($pdo);
        if ($business === '') {
            return '';
        }

        $mode = paypal_mode($pdo);
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
            'notify_url' => base_url('paypal_ipn.php'),
            'custom' => (string) $donationId,
            'invoice' => 'DON-' . $donationId,
            'no_shipping' => '1',
            'charset' => 'utf-8',
            'rm' => '2',
        ];

        return $endpoint . '?' . http_build_query($params);
    }
}
