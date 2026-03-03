<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$donationId = (int) ($_GET['donation_id'] ?? 0);

if ($pdo instanceof PDO && $donationId > 0) {
    try {
        $stmt = $pdo->prepare('UPDATE donations SET payment_status = :payment_status WHERE id = :id');
        $stmt->execute([
            'payment_status' => 'canceled',
            'id' => $donationId,
        ]);
    } catch (Throwable) {
        // Keep UX flow even if DB update fails.
    }
}

set_flash('warning', t('contribute.payment_cancelled'));
redirect('contribute.php');
