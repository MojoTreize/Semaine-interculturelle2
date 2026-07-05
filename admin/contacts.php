<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

/* ── POST actions ─────────────────────────────────────────────────────────── */
if (is_post()) {
    verify_csrf_or_fail();
    $id = (int) post_string('id');
    if (post_string('action') === 'delete' && $id > 0) {
        $pdo->prepare('DELETE FROM contact_messages WHERE id = :id')->execute(['id' => $id]);
        set_flash('success', 'Message supprimé.');
        redirect('admin/contacts.php');
    }
}

/* ── Detail mode ──────────────────────────────────────────────────────────── */
$viewRow = null;
$viewId  = (int) ($_GET['id'] ?? 0);
if ($viewId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE id = :id');
    $stmt->execute(['id' => $viewId]);
    $viewRow = $stmt->fetch() ?: null;
}

/* ── List query ───────────────────────────────────────────────────────────── */
$search = trim((string) ($_GET['q'] ?? ''));
$params = [];
$where  = [];
if ($search !== '') { $where[] = '(full_name LIKE :q OR email LIKE :q OR subject LIKE :q)'; $params['q'] = '%' . $search . '%'; }

$sql = 'SELECT id, full_name, email, subject, message, language, created_at FROM contact_messages';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$adminTitle = t('admin.menu_contacts');
$activeAdmin = 'contacts';
require __DIR__ . '/_header.php';
?>

<?php if ($viewRow): ?>
<section class="card edit-panel">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem">
        <h3>Message de <?= e((string)$viewRow['full_name']) ?> — #<?= e((string)$viewRow['id']) ?></h3>
        <a class="btn btn-muted btn-sm" href="<?= e(admin_url('contacts.php')) ?>">← Retour</a>
    </div>
    <dl class="detail-grid" style="margin-bottom:.9rem">
        <dt>Nom</dt>      <dd><?= e((string)$viewRow['full_name']) ?></dd>
        <dt>Email</dt>    <dd><a href="mailto:<?= e((string)$viewRow['email']) ?>"><?= e((string)$viewRow['email']) ?></a></dd>
        <dt>Objet</dt>    <dd><?= e((string)$viewRow['subject']) ?></dd>
        <dt>Langue</dt>   <dd><?= e((string)$viewRow['language']) ?></dd>
        <dt>Date</dt>     <dd><?= e((string)$viewRow['created_at']) ?></dd>
    </dl>
    <p style="font-weight:700;margin:0 0 .4rem">Message complet</p>
    <div class="full-message"><?= e((string)$viewRow['message']) ?></div>
    <form method="post" style="margin-top:.9rem" onsubmit="return confirm('Supprimer ce message ?')">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= e((string)$viewRow['id']) ?>">
        <button class="btn btn-danger btn-sm" type="submit">Supprimer ce message</button>
    </form>
</section>
<?php endif; ?>

<section class="card">
    <form method="get" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
        <div><label>Recherche</label><input type="text" name="q" value="<?= e($search) ?>" placeholder="Nom, email, objet…" style="width:220px"></div>
        <div style="display:flex;gap:.4rem;align-items:flex-end">
            <button type="submit">Filtrer</button>
            <a class="btn btn-muted" href="<?= e(admin_url('contacts.php')) ?>">Reset</a>
        </div>
    </form>
</section>

<section class="card">
    <p style="color:#4f617e;font-size:.9rem"><?= count($rows) ?> message(s)</p>
    <table>
        <thead>
            <tr><th>ID</th><th>Nom</th><th>Email</th><th>Objet</th><th>Aperçu</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e((string)$row['id']) ?></td>
                <td><?= e((string)$row['full_name']) ?></td>
                <td><?= e((string)$row['email']) ?></td>
                <td><?= e((string)$row['subject']) ?></td>
                <td style="color:#4f617e;font-size:.88rem"><?= e(mb_substr((string)$row['message'], 0, 60)) ?>…</td>
                <td><?= e((string)$row['created_at']) ?></td>
                <td>
                    <div class="table-actions">
                        <a class="btn btn-sm" href="?id=<?= e((string)$row['id']) ?>">Voir</a>
                        <form method="post" onsubmit="return confirm('Supprimer ce message ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= e((string)$row['id']) ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="7">Aucun message.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
