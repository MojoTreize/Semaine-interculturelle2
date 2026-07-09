<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$partnerTypes = ['partner', 'sponsor', 'institutional'];

/* ── POST actions ─────────────────────────────────────────────────────────── */
if (is_post()) {
    verify_csrf_or_fail();
    $action = post_string('action');

    if ($action === 'add_partner') {
        $name        = post_string('name');
        $websiteUrl  = post_string('website_url');
        $partnerType = post_string('partner_type');
        $displayOrder = (int) post_string('display_order');
        $isActive    = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || !in_array($partnerType, $partnerTypes, true)) {
            set_flash('error', t('validation.required'));
            redirect('admin/partners.php');
        }

        $pdo->prepare('INSERT INTO partners (name, website_url, partner_type, display_order, is_active)
                       VALUES (:name, :website_url, :partner_type, :display_order, :is_active)')
            ->execute([
                'name'          => $name,
                'website_url'   => $websiteUrl !== '' ? $websiteUrl : null,
                'partner_type'  => $partnerType,
                'display_order' => $displayOrder,
                'is_active'     => $isActive,
            ]);
        set_flash('success', 'Partenaire ajouté.');
        redirect('admin/partners.php');
    }

    if ($action === 'save_partner') {
        $id = (int) post_string('id');
        if ($id < 1) { redirect('admin/partners.php'); }
        $name        = post_string('name');
        $websiteUrl  = post_string('website_url');
        $partnerType = post_string('partner_type');
        $displayOrder = (int) post_string('display_order');
        $isActive    = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || !in_array($partnerType, $partnerTypes, true)) {
            set_flash('error', t('validation.required'));
            redirect('admin/partners.php?id=' . $id);
        }

        $pdo->prepare('UPDATE partners
            SET name = :name, website_url = :website_url, partner_type = :partner_type,
                display_order = :display_order, is_active = :is_active
            WHERE id = :id')
            ->execute([
                'name'          => $name,
                'website_url'   => $websiteUrl !== '' ? $websiteUrl : null,
                'partner_type'  => $partnerType,
                'display_order' => $displayOrder,
                'is_active'     => $isActive,
                'id'            => $id,
            ]);
        set_flash('success', 'Partenaire mis à jour.');
        redirect('admin/partners.php');
    }

    if ($action === 'delete_partner') {
        $id = (int) post_string('id');
        if ($id > 0) {
            $pdo->prepare('DELETE FROM partners WHERE id = :id')->execute(['id' => $id]);
            set_flash('success', 'Partenaire supprimé.');
        }
        redirect('admin/partners.php');
    }
}

/* ── Edit mode ────────────────────────────────────────────────────────────── */
$editRow = null;
$editId  = (int) ($_GET['id'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM partners WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $editRow = $stmt->fetch() ?: null;
}

$rows = $pdo->query('SELECT id, name, website_url, partner_type, display_order, is_active, created_at
                     FROM partners ORDER BY display_order ASC, id ASC')->fetchAll();

$adminTitle = t('admin.menu_partners');
$activeAdmin = 'partners';
require __DIR__ . '/_header.php';
?>

<!-- Add / Edit form -->
<section class="card <?= $editRow ? 'edit-panel' : '' ?>">
    <h3><?= $editRow ? 'Modifier le partenaire #' . e((string)$editRow['id']) : 'Ajouter un partenaire' ?></h3>
    <form method="post">
        <?= csrf_field() ?>
        <?php if ($editRow): ?>
            <input type="hidden" name="action" value="save_partner">
            <input type="hidden" name="id" value="<?= e((string)$editRow['id']) ?>">
        <?php else: ?>
            <input type="hidden" name="action" value="add_partner">
        <?php endif; ?>

        <div class="row" style="margin-bottom:.75rem">
            <div><label>Nom *</label><input type="text" name="name" value="<?= e((string)($editRow['name']??'')) ?>" required></div>
            <div><label>Site web</label><input type="url" name="website_url" value="<?= e((string)($editRow['website_url']??'')) ?>"></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div>
                <label>Type *</label>
                <select name="partner_type">
                    <?php foreach ($partnerTypes as $type): ?>
                        <option value="<?= e($type) ?>" <?= isset($editRow['partner_type']) && $editRow['partner_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div><label>Ordre d'affichage</label><input type="number" name="display_order" value="<?= e((string)($editRow['display_order']??'0')) ?>"></div>
        </div>
        <p><label><input type="checkbox" name="is_active" value="1" <?= !$editRow || $editRow['is_active'] ? 'checked' : '' ?>> Actif</label></p>
        <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn <?= $editRow ? 'btn-success' : '' ?>"><?= $editRow ? 'Enregistrer' : t('buttons.add') ?></button>
            <?php if ($editRow): ?><a class="btn btn-muted" href="<?= e(admin_url('partners.php')) ?>">Annuler</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <table>
        <thead>
            <tr><th>ID</th><th>Nom</th><th>Site web</th><th>Type</th><th>Actif</th><th>Ordre</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e((string)$row['id']) ?></td>
                <td><?= e((string)$row['name']) ?></td>
                <td><?php if ($row['website_url']): ?><a href="<?= e((string)$row['website_url']) ?>" target="_blank" rel="noopener"><?= e((string)$row['website_url']) ?></a><?php else: ?>—<?php endif; ?></td>
                <td><?= e((string)$row['partner_type']) ?></td>
                <td><?= $row['is_active'] ? '✓' : '✗' ?></td>
                <td><?= e((string)$row['display_order']) ?></td>
                <td>
                    <div class="table-actions">
                        <a class="btn btn-sm" href="?id=<?= e((string)$row['id']) ?>">Modifier</a>
                        <form method="post" onsubmit="return confirm('Supprimer ce partenaire ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_partner">
                            <input type="hidden" name="id" value="<?= e((string)$row['id']) ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="7">Aucun partenaire.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
