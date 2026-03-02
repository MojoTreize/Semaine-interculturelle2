<?php

declare(strict_types=1);

if (!function_exists('array_get')) {
    function array_get(array $array, string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = $array;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}

if (!function_exists('app_config')) {
    function app_config(?string $key = null, mixed $default = null): mixed
    {
        global $config;

        if (!isset($config) || !is_array($config)) {
            return $default;
        }

        if ($key === null) {
            return $config;
        }

        return array_get($config, $key, $default);
    }
}

if (!function_exists('e')) {
    function e(null|string|int|float $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('base_url')) {
    function base_url(string $path = ''): string
    {
        $base = rtrim((string) app_config('app.base_url', ''), '/');

        if ($path === '') {
            return $base;
        }

        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never
    {
        $location = preg_match('#^https?://#i', $path) === 1 ? $path : base_url($path);
        header('Location: ' . $location);
        exit;
    }
}

if (!function_exists('is_post')) {
    function is_post(): bool
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
    }
}

if (!function_exists('post_string')) {
    function post_string(string $key, string $default = ''): string
    {
        if (!isset($_POST[$key])) {
            return $default;
        }

        return trim((string) $_POST[$key]);
    }
}

if (!function_exists('set_flash')) {
    function set_flash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message,
        ];
    }
}

if (!function_exists('get_flash')) {
    function get_flash(): ?array
    {
        if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
            return null;
        }

        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        return $flash;
    }
}

if (!function_exists('remember_old_input')) {
    function remember_old_input(array $input): void
    {
        $_SESSION['old_input'] = $input;
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        if (!isset($_SESSION['old_input']) || !is_array($_SESSION['old_input'])) {
            return $default;
        }

        return trim((string) ($_SESSION['old_input'][$key] ?? $default));
    }
}

if (!function_exists('clear_old_input')) {
    function clear_old_input(): void
    {
        unset($_SESSION['old_input']);
    }
}

if (!function_exists('request_ip')) {
    function request_ip(): ?string
    {
        $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $raw = explode(',', (string) $_SERVER[$key]);
                return trim($raw[0]);
            }
        }

        return null;
    }
}

if (!function_exists('is_valid_email')) {
    function is_valid_email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('normalize_phone')) {
    function normalize_phone(string $phone): string
    {
        return preg_replace('/[^0-9+().\\-\\s]/', '', $phone) ?? '';
    }
}

if (!function_exists('honeypot_passed')) {
    function honeypot_field_name(): string
    {
        return (string) app_config('security.honeypot_field', 'hp_field');
    }

    function honeypot_field_html(): string
    {
        $field = honeypot_field_name();
        return '<div class="hidden-honeypot"><label for="' . e($field) . '">Website</label><input type="text" name="' . e($field) . '" id="' . e($field) . '" autocomplete="off"></div>';
    }

    function honeypot_passed(): bool
    {
        $field = honeypot_field_name();
        return trim((string) ($_POST[$field] ?? '')) === '';
    }
}

if (!function_exists('get_setting')) {
    function get_setting(mixed $pdo, string $key, string $default = ''): string
    {
        static $cache = null;

        if (!is_array($cache)) {
            $cache = [
                'site_domain' => (string) app_config('app.base_url', ''),
                'contact_email' => 'contact@guineedortmund2026.org',
                'organizer_email' => 'organisation@guineedortmund2026.org',
                'bank_holder' => 'Association Guinee Forestiere Allemagne e.V.',
                'bank_iban' => 'DE00 0000 0000 0000 0000 00',
                'bank_bic' => 'GENODE00XXX',
                'bank_name' => 'Banque Exemple Dortmund',
                'stripe_public_key' => (string) app_config('payment.stripe_public_key', ''),
                'stripe_secret_key' => (string) app_config('payment.stripe_secret_key', ''),
                'paypal_business_email' => (string) app_config('payment.paypal_business_email', ''),
                'paypal_mode' => (string) app_config('payment.paypal_mode', 'sandbox'),
                'currency' => (string) app_config('payment.currency', 'EUR'),
                'collection_goal' => '50000',
            ];
        }

        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        try {
            if (is_object($pdo) && method_exists($pdo, 'prepare')) {
                $stmt = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = :setting_key LIMIT 1');
                $stmt->execute(['setting_key' => $key]);
                $value = $stmt->fetchColumn();

                if ($value !== false && $value !== null) {
                    $cache[$key] = (string) $value;
                    return $cache[$key];
                }
            }
        } catch (Throwable) {
            // No persistent storage in no-DB mode.
        }

        $cache[$key] = $default;
        return $cache[$key];
    }
}

if (!function_exists('set_setting')) {
    function set_setting(mixed $pdo, string $key, string $value): void
    {
        // No-op by design while running without SQL persistence.
    }
}

if (!function_exists('format_amount')) {
    function format_amount(float $value, string $currency = 'EUR'): string
    {
        return number_format($value, 2, ',', ' ') . ' ' . strtoupper($currency);
    }
}

if (!function_exists('fetch_program_items')) {
    function fetch_program_items(mixed $pdo, string $lang = 'fr'): array
    {
        $lang = $lang === 'de' ? 'de' : 'fr';

        try {
            $sql = "SELECT id, event_date, start_time, end_time, location, item_type,
                           CASE WHEN :lang = 'de' THEN title_de ELSE title_fr END AS title,
                           CASE WHEN :lang = 'de' THEN description_de ELSE description_fr END AS description
                    FROM program_items
                    WHERE is_active = 1
                    ORDER BY event_date ASC, start_time ASC, display_order ASC, id ASC";

            $stmt = $pdo->prepare($sql);
            $stmt->execute(['lang' => $lang]);
            $rows = $stmt->fetchAll();

            if (!empty($rows)) {
                return $rows;
            }
        } catch (Throwable) {
            // Fallback handled below.
        }

        return fallback_program_items($lang);
    }
}

if (!function_exists('program_by_date')) {
    function program_by_date(array $items): array
    {
        $grouped = [];

        foreach ($items as $item) {
            $date = (string) ($item['event_date'] ?? '');
            if ($date === '') {
                continue;
            }
            $grouped[$date][] = $item;
        }

        ksort($grouped);
        return $grouped;
    }
}

if (!function_exists('program_preview')) {
    function program_preview(mixed $pdo, string $lang = 'fr', int $limit = 4): array
    {
        $items = fetch_program_items($pdo, $lang);
        return array_slice($items, 0, max(1, $limit));
    }
}

if (!function_exists('fetch_featured_speakers')) {
    function fetch_featured_speakers(mixed $pdo): array
    {
        try {
            $stmt = $pdo->query('SELECT full_name, title, organization, bio FROM speakers WHERE is_featured = 1 ORDER BY id ASC');
            $rows = $stmt->fetchAll();
            if (!empty($rows)) {
                return $rows;
            }
        } catch (Throwable) {
            // Fallback below.
        }

        return [
            ['full_name' => 'Dr. Fatou Kaba', 'title' => 'Experte en politiques minieres', 'organization' => 'Consultante independante', 'bio' => 'Specialiste gouvernance et durabilite.'],
            ['full_name' => 'Prof. Amadou Camara', 'title' => 'Economiste du developpement', 'organization' => 'Universite de Conakry', 'bio' => 'Analyse investissement et impact regional.'],
            ['full_name' => 'Mariam Diallo', 'title' => 'Entrepreneure sociale', 'organization' => 'Jeunesse Forestiere Initiative', 'bio' => 'Projets jeunesse et innovation communautaire.'],
        ];
    }
}

if (!function_exists('fetch_active_partners')) {
    function fetch_active_partners(mixed $pdo, int $limit = 0): array
    {
        try {
            $sql = 'SELECT id, name, website_url, logo_path, partner_type
                    FROM partners
                    WHERE is_active = 1
                    ORDER BY display_order ASC, id ASC';
            if ($limit > 0) {
                $sql .= ' LIMIT ' . (int) $limit;
            }
            $stmt = $pdo->query($sql);
            $rows = $stmt->fetchAll();

            if (!empty($rows)) {
                return $rows;
            }
        } catch (Throwable) {
            // Fallback below.
        }

        return [
            ['name' => 'Ville de Dortmund', 'website_url' => 'https://www.dortmund.de', 'logo_path' => null, 'partner_type' => 'institutional'],
            ['name' => 'Diaspora Guinee Forestiere Allemagne', 'website_url' => '#', 'logo_path' => null, 'partner_type' => 'partner'],
            ['name' => 'Chambre de Commerce Guinee - Allemagne', 'website_url' => '#', 'logo_path' => null, 'partner_type' => 'partner'],
        ];
    }
}

if (!function_exists('collection_totals')) {
    function collection_totals(mixed $pdo): array
    {
        try {
            $stmt = $pdo->query("SELECT COALESCE(SUM(amount), 0) AS total_amount, COUNT(*) AS total_count FROM donations WHERE payment_status = 'paid'");
            $row = $stmt->fetch() ?: [];
            return [
                'amount' => (float) ($row['total_amount'] ?? 0),
                'count' => (int) ($row['total_count'] ?? 0),
            ];
        } catch (Throwable) {
            return ['amount' => 0.0, 'count' => 0];
        }
    }
}

if (!function_exists('site_event_start_iso')) {
    function site_event_start_iso(): string
    {
        $start = (string) app_config('app.event_start', '2026-07-04 00:00:00');
        try {
            $date = new DateTimeImmutable($start, new DateTimeZone((string) app_config('app.timezone', 'Europe/Berlin')));
            return $date->format(DateTimeInterface::ATOM);
        } catch (Throwable) {
            return '2026-07-04T00:00:00+02:00';
        }
    }
}

if (!function_exists('output_csv_download')) {
    function output_csv_download(string $filename, array $headers, array $rows): never
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'wb');
        fputcsv($output, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        fclose($output);
        exit;
    }
}

if (!function_exists('admin_url')) {
    function admin_url(string $path = 'index.php'): string
    {
        return base_url('admin/' . ltrim($path, '/'));
    }
}

if (!function_exists('is_https_request')) {
    function is_https_request(): bool
    {
        $https = strtolower((string) ($_SERVER['HTTPS'] ?? ''));
        $forwarded = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));

        return $https === 'on' || $https === '1' || $forwarded === 'https';
    }
}

if (!function_exists('db_is_sqlite')) {
    function db_is_sqlite(mixed $pdo): bool
    {
        try {
            if (!is_object($pdo) || !method_exists($pdo, 'getAttribute')) {
                return false;
            }

            return strtolower((string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) === 'sqlite';
        } catch (Throwable) {
            return false;
        }
    }
}

if (!function_exists('db_now_expression')) {
    function db_now_expression(mixed $pdo): string
    {
        return db_is_sqlite($pdo) ? "datetime('now')" : 'NOW()';
    }
}
