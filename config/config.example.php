<?php

declare(strict_types=1);

/*
 * Production configuration template.
 * Copy this file to config/config.php on the server and fill in the real values.
 * config/config.php is git-ignored and must never contain committed secrets.
 */

return [
    'app' => [
        'name' => 'Semaine de Dialogue Interculturel de la Guinee Forestiere - Dortmund 2026',
        'base_url' => 'https://ugfa-ev.org',
        'timezone' => 'Europe/Berlin',
        'default_lang' => 'fr',
        'supported_langs' => ['fr', 'de'],
        'event_start' => '2026-10-01 00:00:00',
        'event_end' => '2026-10-11 23:59:59',
    ],
    'db' => [
        'driver' => 'mysql', // mysql (production) | sqlite (local dev)
        'path' => dirname(__DIR__) . '/database/dev.sqlite',
        // All-Inkl: use the MySQL host shown in the KAS panel (e.g. dbXXXX.kasserver.com)
        'host' => 'localhost',
        'port' => '3306',
        'name' => 'dXXXXXXX_db',   // database name from KAS
        'user' => 'dXXXXXXX_user', // database user from KAS
        'pass' => 'CHANGE_ME',     // database password from KAS
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        // All-Inkl: create a mailbox in KAS, then use these SMTP settings
        'from_email' => 'no-reply@ugfa-ev.org',
        'from_name' => 'Union de la Guinee Forestière en Allemagne',
        'smtp_host' => 'wXXXXXXX.kasserver.com', // your KAS mail server
        'smtp_port' => 587,
        'smtp_user' => 'no-reply@ugfa-ev.org',
        'smtp_pass' => 'CHANGE_ME',
        'smtp_secure' => 'tls',
        'use_smtp' => true,
    ],
    'security' => [
        'session_name' => 'gd2026_session',
        'csrf_token_name' => 'csrf_token',
        'honeypot_field' => 'hp_field',
    ],
    'admin' => [
        // Change these credentials immediately after the first login
        'email'     => 'admin@ugfa-ev.org',
        'password'  => 'CHANGE_ME',
        'full_name' => 'Administrateur UGFA',
    ],
    'payment' => [
        'currency' => 'EUR',
        'stripe_public_key' => '',
        'stripe_secret_key' => '',
        'stripe_webhook_secret' => '',
        'paypal_client_id' => '',
        'paypal_client_secret' => '',
        'paypal_business_email' => '',
        'paypal_mode' => 'live', // sandbox | live
    ],
];

