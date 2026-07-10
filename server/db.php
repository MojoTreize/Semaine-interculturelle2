<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    initialize_orders_table($pdo);

    return $pdo;
}

function initialize_orders_table(PDO $pdo): void
{
    $driver = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    if ($driver === 'sqlite') {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS orders (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                provider TEXT NOT NULL,
                amount INTEGER NOT NULL,
                currency TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT 'pending',
                provider_ref TEXT DEFAULT NULL,
                created_at TEXT NOT NULL DEFAULT (datetime('now'))
            )"
        );
        $pdo->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_orders_provider_ref ON orders(provider, provider_ref)");
        $pdo->exec("CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status)");
        return;
    }

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS orders (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            provider VARCHAR(20) NOT NULL,
            amount INT UNSIGNED NOT NULL,
            currency CHAR(3) NOT NULL,
            status VARCHAR(20) NOT NULL DEFAULT 'pending',
            provider_ref VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_orders_provider_ref (provider, provider_ref),
            KEY idx_orders_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
    );
}

function create_pending_order(string $provider, int $amount, string $currency): int
{
    if (!in_array($provider, ['stripe', 'paypal'], true)) {
        throw new InvalidArgumentException('Unsupported provider.');
    }
    if ($amount <= 0) {
        throw new InvalidArgumentException('Invalid amount.');
    }
    if (!preg_match('/^[A-Z]{3}$/', strtoupper($currency))) {
        throw new InvalidArgumentException('Invalid currency.');
    }

    $stmt = db()->prepare(
        'INSERT INTO orders (provider, amount, currency, status) VALUES (:provider, :amount, :currency, :status)'
    );
    $stmt->execute([
        'provider' => $provider,
        'amount' => $amount,
        'currency' => strtoupper($currency),
        'status' => 'pending',
    ]);

    return (int) db()->lastInsertId();
}

function update_order_provider_ref(int $orderId, string $providerRef): void
{
    if ($orderId <= 0 || $providerRef === '') {
        throw new InvalidArgumentException('Invalid provider reference.');
    }

    $stmt = db()->prepare('UPDATE orders SET provider_ref = :provider_ref WHERE id = :id');
    $stmt->execute([
        'provider_ref' => $providerRef,
        'id' => $orderId,
    ]);
}

function find_order_by_id(int $orderId): ?array
{
    $stmt = db()->prepare('SELECT * FROM orders WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $orderId]);
    $row = $stmt->fetch();

    return is_array($row) ? $row : null;
}

function find_order_by_provider_ref(string $provider, string $providerRef): ?array
{
    $stmt = db()->prepare('SELECT * FROM orders WHERE provider = :provider AND provider_ref = :provider_ref LIMIT 1');
    $stmt->execute([
        'provider' => $provider,
        'provider_ref' => $providerRef,
    ]);
    $row = $stmt->fetch();

    return is_array($row) ? $row : null;
}

function mark_order_paid(int $orderId): void
{
    $stmt = db()->prepare("UPDATE orders SET status = 'paid' WHERE id = :id AND status = 'pending'");
    $stmt->execute(['id' => $orderId]);
}
