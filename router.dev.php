<?php
// Router for PHP built-in dev server (php -S).
// Apache/Nginx use .htaccess in production — this file is dev only.

$uri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri  = '/' . ltrim((string) $uri, '/');
$base = __DIR__;

// API clean-URL → PHP file mapping
$api = [
    '/api/payment/paypal/create'  => $base . '/api/paypal-create-order.php',
    '/api/payment/paypal/capture' => $base . '/api/paypal-capture-order.php',
    '/api/payment/stripe/session' => $base . '/api/stripe-session.php',
];

if (isset($api[$uri])) {
    require $api[$uri];
    return true;
}

// Redirect .php page URLs to clean URLs
$phpToClean = [
    '/index.php'        => '/',
    '/about.php'        => '/about',
    '/program.php'      => '/program',
    '/registration.php' => '/registration',
    '/contribute.php'   => '/contribute',
    '/partners.php'     => '/partners',
    '/contact.php'      => '/contact',
    '/privacy.php'      => '/privacy',
    '/impressum.php'    => '/impressum',
];
if (isset($phpToClean[$uri])) {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    if ($method === 'GET' || $method === 'HEAD') {
        $qs = $_SERVER['QUERY_STRING'] !== '' ? '?' . $_SERVER['QUERY_STRING'] : '';
        header('Location: ' . $phpToClean[$uri] . $qs, true, 301);
        return true;
    }
    // POST/PUT/PATCH: serve the PHP file directly so POST data is not lost
    require $base . $uri;
    return true;
}

// Serve real files (css, js, images…) directly
$file = $base . $uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// Page clean URLs
$pages = [
    '/about'        => 'about.php',
    '/program'      => 'program.php',
    '/registration' => 'registration.php',
    '/contribute'   => 'contribute.php',
    '/partners'     => 'partners.php',
    '/contact'      => 'contact.php',
    '/privacy'      => 'privacy.php',
    '/impressum'    => 'impressum.php',
];

$path = rtrim($uri, '/');
if (isset($pages[$path])) {
    require $base . '/' . $pages[$path];
    return true;
}

// Default: let PHP serve the file as-is
return false;
