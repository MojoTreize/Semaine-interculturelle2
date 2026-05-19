<?php
declare(strict_types=1);

date_default_timezone_set('UTC');

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value !== false && $value !== '') {
        return $value;
    }

    if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
        return (string) $_ENV[$key];
    }

    if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
        return (string) $_SERVER[$key];
    }

    return $default;
}

function require_env(string $key): string
{
    $value = env_value($key);
    if ($value === null || $value === '') {
        throw new RuntimeException("Environment variable missing: {$key}");
    }

    return $value;
}

define('STRIPE_SECRET_KEY', require_env('STRIPE_SECRET_KEY'));
define('STRIPE_PUBLISHABLE_KEY', require_env('STRIPE_PUBLISHABLE_KEY'));
define('PAYPAL_CLIENT_ID', require_env('PAYPAL_CLIENT_ID'));
define('PAYPAL_SECRET', require_env('PAYPAL_SECRET'));
define('PAYPAL_MODE', strtolower((string) env_value('PAYPAL_MODE', 'sandbox')) === 'live' ? 'live' : 'sandbox');

// Required to verify Stripe webhook signatures.
define('STRIPE_WEBHOOK_SECRET', (string) env_value('STRIPE_WEBHOOK_SECRET', ''));

// Server-side amount. Never trust client amount.
define('ORDER_AMOUNT_CENTS', max(1, (int) env_value('ORDER_AMOUNT_CENTS', '2500')));
define('ORDER_CURRENCY', strtoupper((string) env_value('ORDER_CURRENCY', 'EUR')));

// Optional DB env vars. By default this uses local SQLite.
define('DB_DSN', (string) env_value('DB_DSN', 'sqlite:' . __DIR__ . '/../database/payments.sqlite'));
define('DB_USER', (string) env_value('DB_USER', ''));
define('DB_PASS', (string) env_value('DB_PASS', ''));

function require_http_method(string $method): void
{
    $requestMethod = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
    if ($requestMethod !== strtoupper($method)) {
        json_response(['error' => 'Method not allowed.'], 405);
    }
}

function json_response(array $payload, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function read_json_body(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new InvalidArgumentException('Invalid JSON payload.');
    }

    return $decoded;
}

function app_base_url(): string
{
    $forwardedProto = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $forwardedProto === 'https'
        || ((int) ($_SERVER['SERVER_PORT'] ?? 80) === 443);

    $scheme = $isHttps ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost:8000');

    return $scheme . '://' . $host;
}

function cents_to_decimal_string(int $amountCents): string
{
    return number_format($amountCents / 100, 2, '.', '');
}

function decimal_string_to_cents(string $amount): int
{
    if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
        throw new InvalidArgumentException('Invalid decimal amount format.');
    }

    $parts = explode('.', $amount, 2);
    $whole = (int) $parts[0];
    $fraction = $parts[1] ?? '0';
    $fraction = str_pad(substr($fraction, 0, 2), 2, '0');

    return ($whole * 100) + (int) $fraction;
}

function paypal_base_url(): string
{
    return PAYPAL_MODE === 'live'
        ? 'https://api-m.paypal.com'
        : 'https://api-m.sandbox.paypal.com';
}

function paypal_access_token(): string
{
    $ch = curl_init(paypal_base_url() . '/v1/oauth2/token');
    if ($ch === false) {
        throw new RuntimeException('Cannot initialize PayPal token request.');
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['grant_type' => 'client_credentials']),
        CURLOPT_USERPWD => PAYPAL_CLIENT_ID . ':' . PAYPAL_SECRET,
        CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: fr_FR'],
        CURLOPT_TIMEOUT => 30,
    ]);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('PayPal token request failed: ' . $error);
    }

    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('Unexpected PayPal token response.');
    }

    if ($statusCode < 200 || $statusCode >= 300 || empty($decoded['access_token'])) {
        throw new RuntimeException('Unable to get PayPal access token.');
    }

    return (string) $decoded['access_token'];
}

function paypal_api_request(string $method, string $path, string $accessToken, ?array $body = null): array
{
    $url = paypal_base_url() . $path;
    $ch = curl_init($url);
    if ($ch === false) {
        throw new RuntimeException('Cannot initialize PayPal API request.');
    }

    $headers = [
        'Accept: application/json',
        'Authorization: Bearer ' . $accessToken,
    ];

    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
    ];

    if ($body !== null) {
        $json = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            throw new RuntimeException('Cannot encode PayPal request body.');
        }

        $headers[] = 'Content-Type: application/json';
        $options[CURLOPT_HTTPHEADER] = $headers;
        $options[CURLOPT_POSTFIELDS] = $json;
    }

    curl_setopt_array($ch, $options);

    $raw = curl_exec($ch);
    if ($raw === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new RuntimeException('PayPal API request failed: ' . $error);
    }

    $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        $decoded = ['raw' => $raw];
    }

    return [
        'status' => $statusCode,
        'body' => $decoded,
    ];
}
