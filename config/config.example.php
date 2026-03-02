<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'Semaine de Dialogue Interculturel de la Guinee Forestiere - Dortmund 2026',
        'base_url' => 'https://guineedortmund2026.org',
        'timezone' => 'Europe/Berlin',
        'default_lang' => 'fr',
        'supported_langs' => ['fr', 'de'],
        'event_start' => '2026-07-04 00:00:00',
        'event_end' => '2026-07-13 23:59:59',
    ],
    'db' => [
        'driver' => 'mysql', // mysql | sqlite
        'path' => dirname(__DIR__) . '/database/dev.sqlite',
        'host' => '127.0.0.1',
        'port' => '3306',
        'name' => 'guineedortmund2026',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'from_email' => 'no-reply@guineedortmund2026.org',
        'from_name' => 'Guinee Dortmund 2026',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_user' => 'smtp_user',
        'smtp_pass' => 'smtp_password',
        'smtp_secure' => 'tls',
        'use_smtp' => true,
    ],
    'security' => [
        'session_name' => 'gd2026_session',
        'csrf_token_name' => 'csrf_token',
        'honeypot_field' => 'hp_field',
    ],
    'payment' => [
        'currency' => 'EUR',
        'stripe_public_key' => '',
        'stripe_secret_key' => '',
        'stripe_webhook_secret' => '',
        'paypal_business_email' => '',
        'paypal_mode' => 'sandbox',
    ],
];
