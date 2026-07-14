<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$partnerTypes = [
    'institutional' => 'Association organisatrice',
    'sponsor'       => 'Sponsor',
    'partner'       => 'Partenaire',
];

/* ── Logo upload helper ───────────────────────────────────────────────────── */
function save_partner_logo(int $id): string
{
    if (empty($_FILES['logo']['tmp_name'])) {
        return '';
    }
    $file     = $_FILES['logo'];
    $allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml'];
    $mime     = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed, true) || $file['error'] !== UPLOAD_ERR_OK) {
        return '';
    }
    $ext  = match ($mime) {
        'image/jpeg'   => 'jpg',
        'image/png'    => 'png',
        'image/webp'   => 'webp',
        'image/svg+xml'=> 'svg',
        default        => 'jpg',
    };
    $dir  = ROOT_PATH . '/assets/images/partners/';
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }
    $dest = $dir . 'partner_' . $id . '.' . $ext;
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'assets/images/partners/partner_' . $id . '.' . $ext;
    }
    return '';
}

/* ── POST actions ─────────────────────────────────────────────────────────── */
if (is_post()) {
    verify_csrf_or_fail();
    $action = post_string('action');

    if ($action === 'add_partner') {
        $name         = post_string('name');
        $websiteUrl   = post_string('website_url');
        $partnerType  = post_string('partner_type');
        $vrNumber     = post_string('vr_number');
        $displayOrder = (int) post_string('display_order');
        $isActive     = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || !array_key_exists($partnerType, $partnerTypes)) {
            set_flash('error', t('validation.required'));
            redirect('admin/partners.php');
        }

        $stmt = $pdo->prepare('INSERT INTO partners (name, website_url, partner_type, vr_number, display_order, is_active)
                               VALUES (:name, :website_url, :partner_type, :vr_number, :display_order, :is_active)');
        $stmt->execute([
            'name'          => $name,
            'website_url'   => $websiteUrl !== '' ? $websiteUrl : null,
            'partner_type'  => $partnerType,
            'vr_number'     => $vrNumber !== '' ? $vrNumber : null,
            'display_order' => $displayOrder,
            'is_active'     => $isActive,
        ]);
        $newId = (int) $pdo->lastInsertId();
        $logo  = save_partner_logo($newId);
        if ($logo !== '') {
            $pdo->prepare('UPDATE partners SET logo_path = :logo WHERE id = :id')
                ->execute(['logo' => $logo, 'id' => $newId]);
        }
        set_flash('success', 'Partenaire ajouté.');
        redirect('admin/partners.php');
    }

    if ($action === 'save_partner') {
        $id           = (int) post_string('id');
        if ($id < 1) { redirect('admin/partners.php'); }
        $name         = post_string('name');
        $websiteUrl   = post_string('website_url');
        $partnerType  = post_string('partner_type');
        $vrNumber     = post_string('vr_number');
        $displayOrder = (int) post_string('display_order');
        $isActive     = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || !array_key_exists($partnerType, $partnerTypes)) {
            set_flash('error', t('validation.required'));
            redirect('admin/partners.php?id=' . $id);
        }

        $logo = save_partner_logo($id);
        $pdo->prepare('UPDATE partners
            SET name = :name, website_url = :website_url, partner_type = :partner_type,
                vr_number = :vr_number, display_order = :display_order, is_active = :is_active
                ' . ($logo !== '' ? ', logo_path = :logo' : '') . '
            WHERE id = :id')
            ->execute(array_merge([
                'name'          => $name,
                'website_url'   => $websiteUrl !== '' ? $websiteUrl : null,
                'partner_type'  => $partnerType,
                'vr_number'     => $vrNumber !== '' ? $vrNumber : null,
                'display_order' => $displayOrder,
                'is_active'     => $isActive,
                'id'            => $id,
            ], $logo !== '' ? ['logo' => $logo] : []));

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

$rows = $pdo->query('SELECT id, name, website_url, logo_path, partner_type, vr_number, display_order, is_active, created_at
                     FROM partners ORDER BY partner_type ASC, display_order ASC, id ASC')->fetchAll();

$adminTitle  = t('admin.menu_partners');
$activeAdmin = 'partners';
require __DIR__ . '/_header.php';
?>

<section class="card <?= $editRow ? 'edit-panel' : '' ?>">
    <h3><?= $editRow ? 'Modifier #' . e((string)$editRow['id']) . ' — ' . e((string)$editRow['name']) : 'Ajouter un partenaire / association' ?></h3>
    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if ($editRow): ?>
            <input type="hidden" name="action" value="save_partner">
            <input type="hidden" name="id" value="<?= e((string)$editRow['id']) ?>">
        <?php else: ?>
            <input type="hidden" name="action" value="add_partner">
        <?php endif; ?>

        <div class="row" style="margin-bottom:.75rem">
            <div><label>Nom *</label><input type="text" name="name" value="<?= e((string)($editRow['name']??'')) ?>" required></div>
            <div><label>N° Verein (VR)</label><input type="text" name="vr_number" value="<?= e((string)($editRow['vr_number']??'')) ?>" placeholder="ex. VR 36787 B"></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Site web</label><input type="url" name="website_url" value="<?= e((string)($editRow['website_url']??'')) ?>"></div>
            <div>
                <label>Type *</label>
                <select name="partner_type">
                    <?php foreach ($partnerTypes as $typeKey => $typeLabel): ?>
                        <option value="<?= e($typeKey) ?>" <?= isset($editRow['partner_type']) && $editRow['partner_type'] === $typeKey ? 'selected' : '' ?>><?= e($typeLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div>
                <label>Logo</label>
                <?php if ($editRow && !empty($editRow['logo_path']) && is_file(ROOT_PATH . '/' . $editRow['logo_path'])): ?>
                    <div style="margin-bottom:.5rem;background:#f7f9ff;border:1px solid #d1dded;border-radius:8px;padding:.5rem;display:inline-flex;align-items:center;gap:.75rem">
                        <img src="<?= e(base_url((string)$editRow['logo_path'])) ?>" alt="Logo" style="max-height:48px;max-width:140px;object-fit:contain">
                        <span style="font-size:.8rem;color:#4f617e">Logo actuel</span>
                    </div><br>
                    <label style="font-size:.82rem;color:#4f617e;font-weight:400">Remplacer :</label>
                <?php endif; ?>
                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp,image/svg+xml">
            </div>
            <div><label>Ordre d'affichage</label><input type="number" name="display_order" value="<?= e((string)($editRow['display_order']??'0')) ?>"></div>
        </div>
        <p><label><input type="checkbox" name="is_active" value="1" <?= !$editRow || $editRow['is_active'] ? 'checked' : '' ?>> Actif</label></p>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <button type="submit" class="btn <?= $editRow ? 'btn-success' : '' ?>"><?= $editRow ? 'Enregistrer' : t('buttons.add') ?></button>
            <?php if ($editRow): ?><a class="btn btn-muted" href="<?= e(admin_url('partners.php')) ?>">Annuler</a><?php endif; ?>
        </div>
    </form>
</section>

<section class="card">
    <table>
        <thead>
            <tr><th>Logo</th><th>Nom</th><th>VR</th><th>Site web</th><th>Type</th><th>Actif</th><th>Ordre</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td>
                    <?php if (!empty($row['logo_path']) && is_file(ROOT_PATH . '/' . $row['logo_path'])): ?>
                        <img src="<?= e(base_url((string)$row['logo_path'])) ?>" alt="Logo" style="height:36px;max-width:72px;object-fit:contain;border-radius:4px;border:1px solid #e0e7f0;background:#f9fbff;padding:2px">
                    <?php else: ?>
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:#e8eef8;color:#4f617e;font-size:.75rem;font-weight:700">
                            <?= e(mb_strtoupper(mb_substr(strip_tags((string)$row['name']), 0, 1))) ?>
                        </span>
                    <?php endif; ?>
                </td>
                <td style="font-weight:600"><?= e((string)$row['name']) ?></td>
                <td style="font-size:.82rem;color:#4f617e"><?= e((string)($row['vr_number']??'—')) ?></td>
                <td>
                    <?php if ($row['website_url']): ?>
                        <a href="<?= e((string)$row['website_url']) ?>" target="_blank" rel="noopener" style="font-size:.82rem"><?= e((string)$row['website_url']) ?></a>
                    <?php else: ?>
                        <span style="color:#bbb">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <span style="font-size:.75rem;padding:.15rem .45rem;border-radius:999px;background:<?= $row['partner_type']==='institutional' ? '#e0f2fe' : ($row['partner_type']==='sponsor' ? '#fef9c3' : '#f0fdf4') ?>;color:<?= $row['partner_type']==='institutional' ? '#0369a1' : ($row['partner_type']==='sponsor' ? '#854d0e' : '#166534') ?>;font-weight:600">
                        <?= e($partnerTypes[$row['partner_type']] ?? (string)$row['partner_type']) ?>
                    </span>
                </td>
                <td><?= $row['is_active'] ? '<span style="color:#16a34a;font-weight:700">✓</span>' : '<span style="color:#dc2626">✗</span>' ?></td>
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
        <?php if (!$rows): ?><tr><td colspan="8" style="text-align:center;color:#9ca3af">Aucun partenaire.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
