<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

/* ── POST actions ─────────────────────────────────────────────────────────── */
if (is_post()) {
    verify_csrf_or_fail();
    $id     = (int) post_string('id');
    $action = post_string('action');

    if ($action === 'delete' && $id > 0) {
        $pdo->prepare('DELETE FROM sponsor_requests WHERE id = :id')->execute(['id' => $id]);
        set_flash('success', 'Demande supprimée.');
        redirect('admin/sponsors.php');
    }

    if ($action === 'save' && $id > 0) {
        $stmt = $pdo->prepare('UPDATE sponsor_requests
            SET organization_name  = :organization_name,
                contact_person     = :contact_person,
                email              = :email,
                phone              = :phone,
                website            = :website,
                sponsorship_level  = :sponsorship_level
            WHERE id = :id');
        $stmt->execute([
            'organization_name' => post_string('organization_name'),
            'contact_person'    => post_string('contact_person'),
            'email'             => post_string('email'),
            'phone'             => post_string('phone'),
            'website'           => post_string('website'),
            'sponsorship_level' => post_string('sponsorship_level'),
            'id'                => $id,
        ]);
        set_flash('success', 'Demande mise à jour.');
        redirect('admin/sponsors.php');
    }
}

/* ── Edit/Detail mode ─────────────────────────────────────────────────────── */
$editRow = null;
$editId  = (int) ($_GET['id'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM sponsor_requests WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $editRow = $stmt->fetch() ?: null;
}

/* ── List query ───────────────────────────────────────────────────────────── */
$levels  = ['bronze', 'silver', 'gold', 'strategic'];
$search  = trim((string) ($_GET['q'] ?? ''));
$level   = strtolower((string) ($_GET['level'] ?? ''));
if (!in_array($level, $levels, true)) { $level = ''; }

$where  = [];
$params = [];
if ($level !== '')  { $where[] = 'sponsorship_level = :level'; $params['level'] = $level; }
if ($search !== '') { $where[] = '(organization_name LIKE :q OR contact_person LIKE :q OR email LIKE :q)'; $params['q'] = '%' . $search . '%'; }

$sql = 'SELECT id, organization_name, contact_person, email, phone, website, sponsorship_level, message, created_at FROM sponsor_requests';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$adminTitle = t('admin.menu_sponsors');
$activeAdmin = 'sponsors';
require __DIR__ . '/_header.php';
?>

<?php if ($editRow): ?>
<section class="card edit-panel">
    <h3>Modifier la demande #<?= e((string)$editRow['id']) ?></h3>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e((string)$editRow['id']) ?>">
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Organisation</label><input type="text" name="organization_name" value="<?= e((string)$editRow['organization_name']) ?>" required></div>
            <div><label>Contact</label><input type="text" name="contact_person" value="<?= e((string)$editRow['contact_person']) ?>"></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Email</label><input type="email" name="email" value="<?= e((string)$editRow['email']) ?>"></div>
            <div><label>Téléphone</label><input type="text" name="phone" value="<?= e((string)($editRow['phone']??'')) ?>"></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Site web</label><input type="url" name="website" value="<?= e((string)($editRow['website']??'')) ?>"></div>
            <div>
                <label>Niveau</label>
                <select name="sponsorship_level">
                    <?php foreach ($levels as $lv): ?>
                        <option value="<?= e($lv) ?>" <?= $editRow['sponsorship_level'] === $lv ? 'selected' : '' ?>><?= ucfirst($lv) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php if ($editRow['message'] !== ''): ?>
        <p style="font-weight:700;margin:0 0 .4rem">Message</p>
        <div class="full-message" style="margin-bottom:.75rem"><?= e((string)$editRow['message']) ?></div>
        <?php endif; ?>
        <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn btn-success">Enregistrer</button>
            <a class="btn btn-muted" href="<?= e(admin_url('sponsors.php')) ?>">Annuler</a>
            <form method="post" onsubmit="return confirm('Supprimer cette demande ?')" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= e((string)$editRow['id']) ?>">
                <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
            </form>
        </div>
    </form>
</section>
<?php endif; ?>

<section class="card">
    <form method="get" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
        <div><label>Recherche</label><input type="text" name="q" value="<?= e($search) ?>" placeholder="Organisation, contact…" style="width:200px"></div>
        <div>
            <label>Niveau</label>
            <select name="level">
                <option value="">Tous</option>
                <?php foreach ($levels as $lv): ?>
                    <option value="<?= e($lv) ?>" <?= $level === $lv ? 'selected' : '' ?>><?= ucfirst($lv) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:.4rem;align-items:flex-end">
            <button type="submit">Filtrer</button>
            <a class="btn btn-muted" href="<?= e(admin_url('sponsors.php')) ?>">Reset</a>
        </div>
    </form>
</section>

<section class="card">
    <p style="color:#4f617e;font-size:.9rem"><?= count($rows) ?> demande(s)</p>
    <table>
        <thead>
            <tr><th>ID</th><th>Organisation</th><th>Contact</th><th>Email</th><th>Niveau</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e((string)$row['id']) ?></td>
                <td><?= e((string)$row['organization_name']) ?></td>
                <td><?= e((string)$row['contact_person']) ?></td>
                <td><?= e((string)$row['email']) ?></td>
                <td><?= e((string)$row['sponsorship_level']) ?></td>
                <td><?= e((string)$row['created_at']) ?></td>
                <td>
                    <div class="table-actions">
                        <a class="btn btn-sm" href="?id=<?= e((string)$row['id']) ?>">Modifier</a>
                        <form method="post" onsubmit="return confirm('Supprimer cette demande ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= e((string)$row['id']) ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="7">Aucune demande sponsor.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
