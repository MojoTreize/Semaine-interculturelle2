<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$allowedStatuses  = ['pending', 'paid', 'failed', 'canceled'];
$allowedMethods   = ['stripe', 'paypal', 'bank_transfer'];

/* ── POST actions ─────────────────────────────────────────────────────────── */
if (is_post()) {
    verify_csrf_or_fail();
    $action = post_string('action');
    $id     = (int) post_string('donation_id');

    if ($action === 'delete' && $id > 0) {
        $pdo->prepare('DELETE FROM donations WHERE id = :id')->execute(['id' => $id]);
        set_flash('success', 'Contribution supprimée.');
        redirect('admin/donations.php');
    }

    if ($action === 'save' && $id > 0) {
        $newStatus = post_string('payment_status');
        if (!in_array($newStatus, $allowedStatuses, true)) { $newStatus = 'pending'; }
        $newMethod = post_string('payment_method');
        if (!in_array($newMethod, $allowedMethods, true)) { $newMethod = $allowedMethods[0]; }
        $paidAt = ($newStatus === 'paid') ? (', paid_at = ' . db_now_expression($pdo)) : '';
        try {
            $stmt = $pdo->prepare('UPDATE donations
                SET donor_name     = :donor_name,
                    donor_email    = :donor_email,
                    amount         = :amount,
                    motive         = :motive,
                    payment_method = :payment_method,
                    payment_status = :payment_status' . $paidAt . '
                WHERE id = :id');
            $stmt->execute([
                'donor_name'     => post_string('donor_name'),
                'donor_email'    => post_string('donor_email'),
                'amount'         => (float) post_string('amount'),
                'motive'         => post_string('motive'),
                'payment_method' => $newMethod,
                'payment_status' => $newStatus,
                'id'             => $id,
            ]);
            set_flash('success', 'Contribution mise à jour.');
        } catch (Throwable) {
            set_flash('error', 'Erreur technique lors de la mise à jour.');
        }
        redirect('admin/donations.php');
    }

    /* legacy quick-status buttons */
    if (in_array($action, ['mark_paid', 'mark_pending', 'mark_failed'], true) && $id > 0) {
        $statusMap = ['mark_paid' => 'paid', 'mark_pending' => 'pending', 'mark_failed' => 'failed'];
        $status = $statusMap[$action];
        if ($status === 'paid') {
            $pdo->prepare('UPDATE donations SET payment_status = :s, paid_at = ' . db_now_expression($pdo) . ' WHERE id = :id')
                ->execute(['s' => $status, 'id' => $id]);
        } else {
            $pdo->prepare('UPDATE donations SET payment_status = :s WHERE id = :id')
                ->execute(['s' => $status, 'id' => $id]);
        }
        set_flash('success', 'Statut mis à jour.');
        redirect('admin/donations.php');
    }
}

/* ── Edit mode ────────────────────────────────────────────────────────────── */
$editRow = null;
$editId  = (int) ($_GET['id'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM donations WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $editRow = $stmt->fetch() ?: null;
}

/* ── List query ───────────────────────────────────────────────────────────── */
$statusFilter = strtolower((string) ($_GET['status'] ?? ''));
$search       = trim((string) ($_GET['q'] ?? ''));
if (!in_array($statusFilter, $allowedStatuses, true)) { $statusFilter = ''; }

$where  = [];
$params = [];
if ($statusFilter !== '') { $where[] = 'payment_status = :status'; $params['status'] = $statusFilter; }
if ($search !== '')       { $where[] = '(donor_name LIKE :q OR donor_email LIKE :q OR motive LIKE :q)'; $params['q'] = '%' . $search . '%'; }

$sql = 'SELECT id, donor_name, donor_email, amount, currency, motive, payment_method, payment_status, created_at, paid_at FROM donations';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$adminTitle = t('admin.menu_donations');
$activeAdmin = 'donations';
require __DIR__ . '/_header.php';
?>

<?php if ($editRow): ?>
<section class="card edit-panel">
    <h3>Modifier la contribution #<?= e((string)$editRow['id']) ?></h3>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="donation_id" value="<?= e((string)$editRow['id']) ?>">
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Nom du donateur</label><input type="text" name="donor_name" value="<?= e((string)($editRow['donor_name']??'')) ?>"></div>
            <div><label>Email</label><input type="email" name="donor_email" value="<?= e((string)($editRow['donor_email']??'')) ?>"></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Montant (<?= e((string)$editRow['currency']) ?>)</label><input type="number" step="0.01" min="0" name="amount" value="<?= e((string)$editRow['amount']) ?>"></div>
            <div><label>Motif</label><input type="text" name="motive" value="<?= e((string)($editRow['motive']??'')) ?>"></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div>
                <label>Méthode</label>
                <select name="payment_method">
                    <?php foreach ($allowedMethods as $m): ?>
                        <option value="<?= e($m) ?>" <?= $editRow['payment_method'] === $m ? 'selected' : '' ?>><?= e($m) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Statut</label>
                <select name="payment_status">
                    <?php foreach ($allowedStatuses as $s): ?>
                        <option value="<?= e($s) ?>" <?= $editRow['payment_status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn btn-success">Enregistrer</button>
            <a class="btn btn-muted" href="<?= e(admin_url('donations.php')) ?>">Annuler</a>
        </div>
    </form>
</section>
<?php endif; ?>

<section class="card">
    <form method="get" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
        <div><label>Recherche</label><input type="text" name="q" value="<?= e($search) ?>" placeholder="Nom, email, motif…" style="width:200px"></div>
        <div>
            <label>Statut</label>
            <select name="status">
                <option value="">Tous</option>
                <?php foreach ($allowedStatuses as $s): ?>
                    <option value="<?= e($s) ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:.4rem;align-items:flex-end">
            <button type="submit">Filtrer</button>
            <a class="btn btn-muted" href="<?= e(admin_url('donations.php')) ?>">Reset</a>
        </div>
    </form>
</section>

<section class="card">
    <p style="color:#4f617e;font-size:.9rem"><?= count($rows) ?> résultat(s)</p>
    <table>
        <thead>
            <tr><th>ID</th><th>Donateur</th><th>Montant</th><th>Motif</th><th>Méthode</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e((string)$row['id']) ?></td>
                <td><?= e((string)($row['donor_name'] ?: $row['donor_email'] ?: '—')) ?></td>
                <td><?= e(format_amount((float)$row['amount'], (string)$row['currency'])) ?></td>
                <td><?= e((string)($row['motive']??'—')) ?></td>
                <td><?= e((string)$row['payment_method']) ?></td>
                <td><span class="status-badge status-badge--<?= e((string)$row['payment_status']) ?>"><?= e((string)$row['payment_status']) ?></span></td>
                <td><?= e((string)$row['created_at']) ?></td>
                <td>
                    <div class="table-actions">
                        <a class="btn btn-sm" href="?id=<?= e((string)$row['id']) ?>">Modifier</a>
                        <form method="post" onsubmit="return confirm('Supprimer cette contribution ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="donation_id" value="<?= e((string)$row['id']) ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="8">Aucune contribution.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
