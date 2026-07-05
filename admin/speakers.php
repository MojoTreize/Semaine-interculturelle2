<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$uploadDir = __DIR__ . '/../assets/images/speakers/';

/* ── Handle uploaded photo ────────────────────────────────────────────────── */
$handleUpload = static function (string $fieldName, ?string $oldPath = null) use ($uploadDir): ?string {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $tmp  = $_FILES[$fieldName]['tmp_name'];
    $mime = mime_content_type($tmp);
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
        return null;
    }
    $ext      = $mime === 'image/png' ? 'png' : ($mime === 'image/webp' ? 'webp' : 'jpg');
    $base     = preg_replace('/[^a-z0-9_-]+/i', '_', strtolower(trim(post_string('full_name'))));
    $filename = ($base !== '' ? $base : uniqid('speaker_', true)) . '.' . $ext;
    $dest     = $uploadDir . $filename;
    if (move_uploaded_file($tmp, $dest)) {
        if ($oldPath !== null && $oldPath !== '') {
            $oldFull = __DIR__ . '/../' . ltrim($oldPath, '/');
            if (is_file($oldFull) && str_contains($oldFull, '/assets/images/speakers/')) {
                @unlink($oldFull);
            }
        }
        return 'assets/images/speakers/' . $filename;
    }
    return null;
};

$deletePhoto = static function (string $path): void {
    $full = __DIR__ . '/../' . ltrim($path, '/');
    if (is_file($full) && str_contains($full, '/assets/images/speakers/')) {
        @unlink($full);
    }
};

/* ── POST actions ─────────────────────────────────────────────────────────── */
if (is_post()) {
    verify_csrf_or_fail();
    $action = post_string('action');
    $id     = (int) post_string('id');

    if ($action === 'add') {
        $photoPath = $handleUpload('photo');
        $pdo->prepare('INSERT INTO speakers (full_name, title, organization, bio, photo_path, is_featured)
                        VALUES (:full_name, :title, :organization, :bio, :photo_path, :is_featured)')
            ->execute([
                'full_name'    => post_string('full_name'),
                'title'        => post_string('title')        ?: null,
                'organization' => post_string('organization') ?: null,
                'bio'          => post_string('bio')          ?: null,
                'photo_path'   => $photoPath,
                'is_featured'  => isset($_POST['is_featured']) ? 1 : 0,
            ]);
        set_flash('success', 'Intervenant ajouté.');
        redirect('admin/speakers.php');
    }

    if ($action === 'save' && $id > 0) {
        $stmt = $pdo->prepare('SELECT photo_path FROM speakers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $oldPhoto  = (string) ($stmt->fetchColumn() ?? '');
        $photoPath = $handleUpload('photo', $oldPhoto) ?? ($oldPhoto ?: null);

        $pdo->prepare('UPDATE speakers SET
                full_name    = :full_name,
                title        = :title,
                organization = :organization,
                bio          = :bio,
                photo_path   = :photo_path,
                is_featured  = :is_featured,
                updated_at   = CURRENT_TIMESTAMP
            WHERE id = :id')
            ->execute([
                'full_name'    => post_string('full_name'),
                'title'        => post_string('title')        ?: null,
                'organization' => post_string('organization') ?: null,
                'bio'          => post_string('bio')          ?: null,
                'photo_path'   => $photoPath,
                'is_featured'  => isset($_POST['is_featured']) ? 1 : 0,
                'id'           => $id,
            ]);
        set_flash('success', 'Intervenant mis à jour.');
        redirect('admin/speakers.php');
    }

    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare('SELECT photo_path FROM speakers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $photo = (string) ($stmt->fetchColumn() ?? '');
        if ($photo !== '') { $deletePhoto($photo); }
        $pdo->prepare('DELETE FROM speakers WHERE id = :id')->execute(['id' => $id]);
        set_flash('success', 'Intervenant supprimé.');
        redirect('admin/speakers.php');
    }

    if ($action === 'toggle_featured' && $id > 0) {
        $pdo->prepare('UPDATE speakers SET is_featured = CASE WHEN is_featured = 1 THEN 0 ELSE 1 END WHERE id = :id')
            ->execute(['id' => $id]);
        redirect('admin/speakers.php');
    }

    if ($action === 'remove_photo' && $id > 0) {
        $stmt = $pdo->prepare('SELECT photo_path FROM speakers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $photo = (string) ($stmt->fetchColumn() ?? '');
        if ($photo !== '') {
            $deletePhoto($photo);
            $pdo->prepare('UPDATE speakers SET photo_path = NULL WHERE id = :id')->execute(['id' => $id]);
        }
        redirect('admin/speakers.php?id=' . $id);
    }
}

/* ── Edit mode ────────────────────────────────────────────────────────────── */
$editRow = null;
$editId  = (int) ($_GET['id'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM speakers WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $editRow = $stmt->fetch() ?: null;
}

/* ── List ─────────────────────────────────────────────────────────────────── */
$rows = $pdo->query('SELECT * FROM speakers ORDER BY is_featured DESC, id ASC')->fetchAll();

$adminTitle  = 'Intervenants & Panélistes';
$activeAdmin = 'speakers';
require __DIR__ . '/_header.php';
?>

<!-- Add / Edit form -->
<section class="card <?= $editRow ? 'edit-panel' : '' ?>">
    <h3><?= $editRow ? 'Modifier l\'intervenant #' . e((string) $editRow['id']) : 'Ajouter un intervenant' ?></h3>

    <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if ($editRow): ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id"     value="<?= e((string) $editRow['id']) ?>">
        <?php else: ?>
            <input type="hidden" name="action" value="add">
        <?php endif; ?>

        <?php if ($editRow && !empty($editRow['photo_path'])): ?>
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;padding:.75rem;background:#f8faff;border-radius:8px">
                <img src="<?= e(base_url((string) $editRow['photo_path'])) ?>"
                     alt="photo actuelle"
                     style="width:72px;height:72px;border-radius:50%;object-fit:cover;border:2px solid var(--color-primary)">
                <div>
                    <p style="margin:0 0 .4rem;font-size:.85rem;color:#4f617e">Photo actuelle</p>
                    <form method="post" style="margin:0;display:inline">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="remove_photo">
                        <input type="hidden" name="id"     value="<?= e((string) $editRow['id']) ?>">
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Supprimer cette photo ?')">Supprimer la photo</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <div class="row" style="margin-bottom:.75rem">
            <div>
                <label>Nom complet *</label>
                <input type="text" name="full_name"
                       value="<?= e((string) ($editRow['full_name'] ?? '')) ?>" required>
            </div>
            <div>
                <label>Photo <?= $editRow && !empty($editRow['photo_path']) ? '(remplacer)' : '' ?>
                    <small style="font-weight:400;color:#4f617e">JPG / PNG / WebP</small>
                </label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                       style="padding:.35rem 0">
            </div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div>
                <label>Titre / Fonction</label>
                <input type="text" name="title"
                       value="<?= e((string) ($editRow['title'] ?? '')) ?>"
                       placeholder="Ancien Premier Ministre">
            </div>
            <div>
                <label>Organisation / Institution</label>
                <input type="text" name="organization"
                       value="<?= e((string) ($editRow['organization'] ?? '')) ?>"
                       placeholder="Nations-Unies">
            </div>
        </div>
        <div style="margin-bottom:.75rem">
            <label>Biographie / Rôle dans l'événement</label>
            <textarea name="bio" rows="3"
                      placeholder="Décrivez le rôle ou le profil de l'intervenant..."><?= e((string) ($editRow['bio'] ?? '')) ?></textarea>
        </div>
        <p style="margin-bottom:.85rem">
            <label style="display:flex;align-items:center;gap:.5rem;cursor:pointer">
                <input type="checkbox" name="is_featured" value="1"
                    <?= (!$editRow || !empty($editRow['is_featured'])) ? 'checked' : '' ?>>
                Afficher dans la vitrine "Intervenants et experts" sur la page Programme
            </label>
        </p>
        <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn <?= $editRow ? 'btn-success' : '' ?>">
                <?= $editRow ? 'Enregistrer les modifications' : 'Ajouter l\'intervenant' ?>
            </button>
            <?php if ($editRow): ?>
                <a class="btn btn-muted" href="<?= e(admin_url('speakers.php')) ?>">Annuler</a>
            <?php endif; ?>
        </div>
    </form>
</section>

<!-- List -->
<section class="card">
    <p style="color:#4f617e;font-size:.9rem"><?= count($rows) ?> intervenant(s) enregistré(s)</p>
    <table>
        <thead>
            <tr>
                <th>Photo</th>
                <th>Nom</th>
                <th>Titre</th>
                <th>Organisation</th>
                <th>Vitrine</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <?php
            $photo    = trim((string) ($row['photo_path'] ?? ''));
            $hasPhoto = $photo !== '' && file_exists(__DIR__ . '/../' . ltrim($photo, '/'));
            $initials = implode('', array_map(
                static fn($w) => mb_strtoupper(mb_substr($w, 0, 1)),
                array_slice(explode(' ', (string) $row['full_name']), 0, 2)
            ));
            ?>
            <tr>
                <td>
                    <?php if ($hasPhoto): ?>
                        <img src="<?= e(base_url($photo)) ?>" alt=""
                             style="width:46px;height:46px;border-radius:50%;object-fit:cover;border:2px solid var(--color-primary)">
                    <?php else: ?>
                        <div style="width:46px;height:46px;border-radius:50%;background:var(--color-primary);
                                    color:#fff;display:flex;align-items:center;justify-content:center;
                                    font-weight:700;font-size:.8rem;letter-spacing:.03em">
                            <?= e($initials) ?>
                        </div>
                    <?php endif; ?>
                </td>
                <td><strong><?= e((string) $row['full_name']) ?></strong></td>
                <td style="font-size:.85rem"><?= e((string) ($row['title'] ?? '—')) ?></td>
                <td style="font-size:.85rem"><?= e((string) ($row['organization'] ?? '—')) ?></td>
                <td>
                    <form method="post" style="margin:0">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="toggle_featured">
                        <input type="hidden" name="id"     value="<?= e((string) $row['id']) ?>">
                        <button type="submit"
                                class="btn btn-sm <?= $row['is_featured'] ? 'btn-success' : 'btn-muted' ?>"
                                title="Cliquer pour basculer">
                            <?= $row['is_featured'] ? '★ Vedette' : '☆ Masqué' ?>
                        </button>
                    </form>
                </td>
                <td>
                    <div class="table-actions">
                        <a class="btn btn-sm" href="?id=<?= e((string) $row['id']) ?>">Modifier</a>
                        <form method="post" onsubmit="return confirm('Supprimer cet intervenant et sa photo ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id"     value="<?= e((string) $row['id']) ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
            <tr><td colspan="6" style="text-align:center;color:#4f617e">Aucun intervenant enregistré.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
