<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

if (is_post()) {
    verify_csrf_or_fail();
    $action = post_string('action');
    $donationId = (int) post_string('donation_id');

    if ($donationId > 0 && in_array($action, ['mark_paid', 'mark_pending', 'mark_failed'], true)) {
        $statusMap = [
            'mark_paid' => 'paid',
            'mark_pending' => 'pending',
            'mark_failed' => 'failed',
        ];
        $status = $statusMap[$action];
        if ($status === 'paid') {
            $stmt = $pdo->prepare('UPDATE donations SET payment_status = :status, paid_at = ' . db_now_expression($pdo) . ' WHERE id = :id');
        } else {
            $stmt = $pdo->prepare('UPDATE donations SET payment_status = :status WHERE id = :id');
        }
        $stmt->execute([
            'status' => $status,
            'id' => $donationId,
        ]);
        set_flash('success', 'Statut mis a jour.');
    }
    redirect('admin/donations.php');
}

$statusFilter = strtolower((string) ($_GET['status'] ?? ''));
$allowedStatuses = ['pending', 'paid', 'failed', 'canceled'];
if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

$sql = 'SELECT id, donor_name, donor_email, amount, currency, motive, payment_method, payment_status, created_at, paid_at
        FROM donations';
$params = [];
if ($statusFilter !== '') {
    $sql .= ' WHERE payment_status = :status';
    $params['status'] = $statusFilter;
}
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$adminTitle = t('admin.menu_donations');
$activeAdmin = 'donations';
require __DIR__ . '/_header.php';
?>

<section class="card">
    <form method="get" class="row">
        <div>
            <label for="status"><?= e(t('admin.filter')) ?></label>
            <select name="status" id="status">
                <option value="">Tous</option>
                <?php foreach ($allowedStatuses as $status): ?>
                    <option value="<?= e($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= e($status) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;align-items:flex-end;">
            <button type="submit"><?= e(t('admin.filter')) ?></button>
        </div>
    </form>
</section>

<section class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Donateur</th>
                <th>Montant</th>
                <th>Motif</th>
                <th>Methode</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e((string) $row['id']) ?></td>
                <td><?= e((string) ($row['donor_name'] ?: $row['donor_email'] ?: '-')) ?></td>
                <td><?= e(format_amount((float) $row['amount'], (string) $row['currency'])) ?></td>
                <td><?= e((string) $row['motive']) ?></td>
                <td><?= e((string) $row['payment_method']) ?></td>
                <td><?= e((string) $row['payment_status']) ?></td>
                <td><?= e((string) $row['created_at']) ?></td>
                <td>
                    <form method="post" style="display:flex;gap:0.4rem;flex-wrap:wrap;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="donation_id" value="<?= e((string) $row['id']) ?>">
                        <button type="submit" name="action" value="mark_paid">Paid</button>
                        <button type="submit" name="action" value="mark_pending">Pending</button>
                        <button type="submit" name="action" value="mark_failed">Failed</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
