<?php

declare(strict_types=1);

return [
    'app' => [
        'name' => 'Semaine de Coopération Internationale et de Dialogue Interculturelle de la Guinée Forestière en Allemagne',
        'base_url' => 'http://127.0.0.1:8000',
        'timezone' => 'Europe/Berlin',
        'default_lang' => 'fr',
        'supported_langs' => ['fr', 'de'],
        'event_start' => '2026-10-01 00:00:00',
        'event_end' => '2026-10-11 23:59:59',
    ],
    'db' => [
        'driver'  => 'mysql',
        'path'    => '',
        'host'    => '127.0.0.1',
        'port'    => '3306',
        'name'    => 'semaine_interculturelle',
        'user'    => 'semaine_dev',
        'pass'    => 'devpass2026',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'from_email'  => 'no-reply@ugfa-ev.org',
        'from_name'   => 'Union de la Guinee Forestière en Allemagne',
        'smtp_host'   => 'localhost',
        'smtp_port'   => 587,
        'smtp_user'   => '',
        'smtp_pass'   => '',
        'smtp_secure' => 'tls',
        'use_smtp'    => false,
    ],
    'security' => [
        'session_name'    => 'gd2026_session',
        'csrf_token_name' => 'csrf_token',
        'honeypot_field'  => 'hp_field',
    ],
    'admin' => [
        'email'     => 'admin@ugfa-ev.org',
        'password'  => 'Admin@1234',
        'full_name' => 'Administrateur UGFA',
    ],
    'payment' => [
        'currency'               => 'EUR',
        'stripe_public_key'      => '',
        'stripe_secret_key'      => '',
        'stripe_webhook_secret'  => '',
        'paypal_client_id'       => 'BAALuN9YZg72PpQt2q0bk8M5QJ_zs7x4HzOS2UtpMEHlB7pXeD8cWUKm5ZGoDre9B2341QtfCnDIUq0sOY',
        'paypal_client_secret'   => 'EIUoE_jj7WC1hAhzLqu168KottsgtRpk9h8nCzBMkUa6j8dI4LNEwpPMTtVTfJ30TmhA0uHjT83BFqiu',
        'paypal_business_email'  => 'nestor.thea@ugfa-ev.org',
        'paypal_mode'            => 'live',
    ],
];
