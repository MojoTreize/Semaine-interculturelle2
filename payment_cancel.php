<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$donationId = (int) ($_GET['donation_id'] ?? 0);
if ($donationId > 0) {
    $stmt = $pdo->prepare('UPDATE donations SET payment_status = :status WHERE id = :id AND payment_status = :current');
    $stmt->execute([
        'status' => 'canceled',
        'id' => $donationId,
        'current' => 'pending',
    ]);
}

set_flash('warning', t('contribute.payment_cancelled'));
redirect('contribute.php');
