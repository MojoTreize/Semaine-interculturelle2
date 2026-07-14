<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$typeOptions = ['participant', 'partner', 'speaker', 'sponsor'];

/* ── POST actions ─────────────────────────────────────────────────────────── */
if (is_post()) {
    verify_csrf_or_fail();
    $action = post_string('action');
    $id     = (int) post_string('id');

    if ($action === 'delete' && $id > 0) {
        $pdo->prepare('DELETE FROM registrations WHERE id = :id')->execute(['id' => $id]);
        set_flash('success', 'Inscription supprimée.');
        redirect('admin/registrations.php');
    }

    if ($action === 'save' && $id > 0) {
        $stmt = $pdo->prepare('UPDATE registrations
            SET first_name = :first_name,
                last_name  = :last_name,
                email      = :email,
                phone      = :phone,
                country    = :country,
                organization = :organization,
                participation_type = :participation_type
            WHERE id = :id');
        $stmt->execute([
            'first_name'         => post_string('first_name'),
            'last_name'          => post_string('last_name'),
            'email'              => post_string('email'),
            'phone'              => post_string('phone'),
            'country'            => post_string('country'),
            'organization'       => post_string('organization'),
            'participation_type' => post_string('participation_type'),
            'id'                 => $id,
        ]);
        set_flash('success', 'Inscription mise à jour.');
        redirect('admin/registrations.php');
    }
}

/* ── Edit mode ────────────────────────────────────────────────────────────── */
$editRow  = null;
$editId   = (int) ($_GET['id'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM registrations WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $editRow = $stmt->fetch() ?: null;
}

/* ── List query ───────────────────────────────────────────────────────────── */
$typeFilter = strtolower((string) ($_GET['type'] ?? ''));
$search     = trim((string) ($_GET['q'] ?? ''));
if (!in_array($typeFilter, $typeOptions, true)) { $typeFilter = ''; }

$where  = [];
$params = [];
if ($typeFilter !== '') { $where[] = 'participation_type = :type'; $params['type'] = $typeFilter; }
if ($search !== '')     { $where[] = '(first_name LIKE :q OR last_name LIKE :q OR email LIKE :q OR country LIKE :q)'; $params['q'] = '%' . $search . '%'; }

$sql  = 'SELECT id, first_name, last_name, country, email, phone, organization, participation_type, language, created_at FROM registrations';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

/* ── Export ───────────────────────────────────────────────────────────────── */
$export = strtolower((string) ($_GET['export'] ?? ''));
if ($export === 'csv') {
    $fileBase = 'registrations-' . date('Ymd-His');
    $headers  = ['ID', 'Prénom', 'Nom', 'Pays', 'Email', 'Téléphone', 'Organisation', 'Type', 'Langue', 'Date'];
    $dataRows = [];
    foreach ($rows as $r) {
        $dataRows[] = [(string)$r['id'], (string)$r['first_name'], (string)$r['last_name'], (string)$r['country'],
                       (string)$r['email'], (string)$r['phone'], (string)($r['organization']??''),
                       (string)$r['participation_type'], (string)$r['language'], (string)$r['created_at']];
    }
    output_csv_download($fileBase . '.csv', $headers, $dataRows);
}

$adminTitle = t('admin.menu_registrations');
$activeAdmin = 'registrations';
require __DIR__ . '/_header.php';
?>

<?php if ($editRow): ?>
<section class="card edit-panel">
    <h3>Modifier l'inscription #<?= e((string)$editRow['id']) ?></h3>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e((string)$editRow['id']) ?>">
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Prénom</label><input type="text" name="first_name" value="<?= e((string)$editRow['first_name']) ?>" required></div>
            <div><label>Nom</label><input type="text" name="last_name" value="<?= e((string)$editRow['last_name']) ?>" required></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Email</label><input type="email" name="email" value="<?= e((string)$editRow['email']) ?>"></div>
            <div><label>Téléphone</label><input type="text" name="phone" value="<?= e((string)$editRow['phone']) ?>"></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Pays</label><input type="text" name="country" value="<?= e((string)$editRow['country']) ?>"></div>
            <div><label>Organisation</label><input type="text" name="organization" value="<?= e((string)($editRow['organization']??'')) ?>"></div>
        </div>
        <div style="margin-bottom:.75rem">
            <label>Type de participation</label>
            <select name="participation_type">
                <?php foreach ($typeOptions as $t): ?>
                    <option value="<?= e($t) ?>" <?= $editRow['participation_type'] === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:.5rem">
            <button type="submit">Enregistrer</button>
            <a class="btn btn-muted" href="<?= e(admin_url('registrations.php')) ?>">Annuler</a>
        </div>
    </form>
</section>
<?php endif; ?>

<section class="card">
    <form method="get" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
        <div><label>Recherche</label><input type="text" name="q" value="<?= e($search) ?>" placeholder="Nom, email, pays…" style="width:200px"></div>
        <div>
            <label>Type</label>
            <select name="type">
                <option value="">Tous</option>
                <?php foreach ($typeOptions as $t): ?>
                    <option value="<?= e($t) ?>" <?= $typeFilter === $t ? 'selected' : '' ?>><?= e($t) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:.4rem;align-items:flex-end">
            <button type="submit">Filtrer</button>
            <a class="btn" href="<?= e(admin_url('registrations.php?' . http_build_query(['q'=>$search,'type'=>$typeFilter,'export'=>'csv']))) ?>">Export CSV</a>
            <a class="btn btn-muted" href="<?= e(admin_url('registrations.php')) ?>">Reset</a>
        </div>
    </form>
</section>

<section class="card">
    <p style="color:#4f617e;font-size:.9rem"><?= count($rows) ?> résultat(s)</p>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Nom</th><th>Email</th><th>Pays</th><th>Type</th><th>Date</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e((string)$row['id']) ?></td>
                <td><?= e($row['first_name'] . ' ' . $row['last_name']) ?></td>
                <td><?= e((string)$row['email']) ?></td>
                <td><?= e((string)$row['country']) ?></td>
                <td><?= e((string)$row['participation_type']) ?></td>
                <td><?= e((string)$row['created_at']) ?></td>
                <td>
                    <div class="table-actions">
                        <a class="btn btn-sm" href="?id=<?= e((string)$row['id']) ?>">Modifier</a>
                        <form method="post" onsubmit="return confirm('Supprimer cette inscription ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= e((string)$row['id']) ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="7">Aucune inscription.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
