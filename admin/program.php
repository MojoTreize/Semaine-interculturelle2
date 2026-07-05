<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$itemTypes = ['conference', 'panel', 'exhibition', 'networking', 'ceremony', 'workshop'];

/* ── POST actions ─────────────────────────────────────────────────────────── */
if (is_post()) {
    verify_csrf_or_fail();
    $action = post_string('action');

    $buildPayload = static function () use ($itemTypes): array {
        $eventDate = post_string('event_date');
        $startTime = post_string('start_time');
        $endTime   = post_string('end_time');
        $titleFr   = post_string('title_fr');
        $titleDe   = post_string('title_de');
        $itemType  = post_string('item_type');

        if ($eventDate === '' || $titleFr === '' || $titleDe === '' || !in_array($itemType, $itemTypes, true)) {
            return [];
        }

        $speakersRaw = trim(post_string('speakers_list'));

        return [
            'event_date'     => $eventDate,
            'start_time'     => $startTime !== '' ? $startTime : null,
            'end_time'       => $endTime !== '' ? $endTime : null,
            'title_fr'       => $titleFr,
            'title_de'       => $titleDe,
            'description_fr' => post_string('description_fr') !== '' ? post_string('description_fr') : null,
            'description_de' => post_string('description_de') !== '' ? post_string('description_de') : null,
            'location'       => post_string('location') !== '' ? post_string('location') : null,
            'item_type'      => $itemType,
            'display_order'  => (int) post_string('display_order'),
            'is_active'      => isset($_POST['is_active']) ? 1 : 0,
            'speakers_list'  => $speakersRaw !== '' ? $speakersRaw : null,
        ];
    };

    if ($action === 'add_item') {
        $payload = $buildPayload();
        if (empty($payload)) {
            set_flash('error', t('validation.required'));
            redirect('admin/program.php');
        }
        $pdo->prepare('INSERT INTO program_items
            (event_date, start_time, end_time, title_fr, title_de, description_fr, description_de, location, item_type, display_order, is_active, speakers_list)
            VALUES
            (:event_date, :start_time, :end_time, :title_fr, :title_de, :description_fr, :description_de, :location, :item_type, :display_order, :is_active, :speakers_list)')
            ->execute($payload);
        set_flash('success', 'Élément du programme ajouté.');
        redirect('admin/program.php');
    }

    if ($action === 'save_item') {
        $id = (int) post_string('id');
        if ($id < 1) { redirect('admin/program.php'); }
        $payload = $buildPayload();
        if (empty($payload)) {
            set_flash('error', t('validation.required'));
            redirect('admin/program.php?id=' . $id);
        }
        $payload['id'] = $id;
        $pdo->prepare('UPDATE program_items SET
            event_date = :event_date, start_time = :start_time, end_time = :end_time,
            title_fr = :title_fr, title_de = :title_de,
            description_fr = :description_fr, description_de = :description_de,
            location = :location, item_type = :item_type,
            display_order = :display_order, is_active = :is_active,
            speakers_list = :speakers_list
            WHERE id = :id')
            ->execute($payload);
        set_flash('success', 'Élément mis à jour.');
        redirect('admin/program.php');
    }

    if ($action === 'delete_item') {
        $id = (int) post_string('id');
        if ($id > 0) {
            $pdo->prepare('DELETE FROM program_items WHERE id = :id')->execute(['id' => $id]);
            set_flash('success', 'Élément supprimé.');
        }
        redirect('admin/program.php');
    }
}

/* ── Edit mode ────────────────────────────────────────────────────────────── */
$editRow = null;
$editId  = (int) ($_GET['id'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM program_items WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $editRow = $stmt->fetch() ?: null;
}

/* ── Filter ───────────────────────────────────────────────────────────────── */
$dateFilter = (string) ($_GET['date'] ?? '');
$typeFilter = (string) ($_GET['type'] ?? '');
if (!in_array($typeFilter, $itemTypes, true)) { $typeFilter = ''; }

$where  = [];
$params = [];
if ($dateFilter !== '') { $where[] = 'event_date = :date'; $params['date'] = $dateFilter; }
if ($typeFilter !== '') { $where[] = 'item_type = :type';  $params['type'] = $typeFilter; }

$sql = 'SELECT id, event_date, start_time, end_time, title_fr, title_de, location, item_type, is_active, display_order FROM program_items';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY event_date ASC, start_time ASC, display_order ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$adminTitle = t('admin.menu_program');
$activeAdmin = 'program';
require __DIR__ . '/_header.php';
?>

<!-- Add / Edit form -->
<section class="card <?= $editRow ? 'edit-panel' : '' ?>">
    <h3><?= $editRow ? 'Modifier l\'élément #' . e((string)$editRow['id']) : 'Ajouter un élément de programme' ?></h3>
    <form method="post">
        <?= csrf_field() ?>
        <?php if ($editRow): ?>
            <input type="hidden" name="action" value="save_item">
            <input type="hidden" name="id" value="<?= e((string)$editRow['id']) ?>">
        <?php else: ?>
            <input type="hidden" name="action" value="add_item">
        <?php endif; ?>

        <div class="row" style="margin-bottom:.75rem">
            <div><label>Date *</label><input type="date" name="event_date" value="<?= e((string)($editRow['event_date']??'')) ?>" required></div>
            <div>
                <label>Type *</label>
                <select name="item_type">
                    <?php foreach ($itemTypes as $type): ?>
                        <option value="<?= e($type) ?>" <?= isset($editRow['item_type']) && $editRow['item_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Heure début</label><input type="time" name="start_time" value="<?= e(substr((string)($editRow['start_time']??''), 0, 5)) ?>"></div>
            <div><label>Heure fin</label><input type="time" name="end_time" value="<?= e(substr((string)($editRow['end_time']??''), 0, 5)) ?>"></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Titre FR *</label><input type="text" name="title_fr" value="<?= e((string)($editRow['title_fr']??'')) ?>" required></div>
            <div><label>Titre DE *</label><input type="text" name="title_de" value="<?= e((string)($editRow['title_de']??'')) ?>" required></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Description FR</label><textarea name="description_fr" rows="3"><?= e((string)($editRow['description_fr']??'')) ?></textarea></div>
            <div><label>Description DE</label><textarea name="description_de" rows="3"><?= e((string)($editRow['description_de']??'')) ?></textarea></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Lieu</label><input type="text" name="location" value="<?= e((string)($editRow['location']??'')) ?>"></div>
            <div><label>Ordre d'affichage</label><input type="number" name="display_order" value="<?= e((string)($editRow['display_order']??'0')) ?>"></div>
        </div>
        <div style="margin-bottom:.75rem">
            <label>Intervenants / Panélistes <small style="font-weight:400;color:#4f617e">(un nom par ligne)</small></label>
            <textarea name="speakers_list" rows="4" placeholder="Dr. Fatou Kaba — Experte en politiques minières&#10;Prof. Amadou Camara — Économiste du développement&#10;Mariam Diallo"><?= e((string)($editRow['speakers_list']??'')) ?></textarea>
        </div>
        <p><label><input type="checkbox" name="is_active" value="1" <?= !$editRow || $editRow['is_active'] ? 'checked' : '' ?>> Actif</label></p>
        <div style="display:flex;gap:.5rem">
            <button type="submit" class="btn <?= $editRow ? 'btn-success' : '' ?>"><?= $editRow ? 'Enregistrer' : t('buttons.add') ?></button>
            <?php if ($editRow): ?><a class="btn btn-muted" href="<?= e(admin_url('program.php')) ?>">Annuler</a><?php endif; ?>
        </div>
    </form>
</section>

<!-- Filter -->
<section class="card">
    <form method="get" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
        <div><label>Date</label><input type="date" name="date" value="<?= e($dateFilter) ?>" style="width:auto"></div>
        <div>
            <label>Type</label>
            <select name="type">
                <option value="">Tous</option>
                <?php foreach ($itemTypes as $type): ?>
                    <option value="<?= e($type) ?>" <?= $typeFilter === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:.4rem;align-items:flex-end">
            <button type="submit">Filtrer</button>
            <a class="btn btn-muted" href="<?= e(admin_url('program.php')) ?>">Reset</a>
        </div>
    </form>
</section>

<section class="card">
    <p style="color:#4f617e;font-size:.9rem"><?= count($rows) ?> élément(s)</p>
    <table>
        <thead>
            <tr><th>ID</th><th>Date</th><th>Horaire</th><th>Titre FR</th><th>Type</th><th>Lieu</th><th>Actif</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e((string)$row['id']) ?></td>
                <td><?= e((string)$row['event_date']) ?></td>
                <td><?= e(substr((string)($row['start_time']??''), 0, 5)) ?>–<?= e(substr((string)($row['end_time']??''), 0, 5)) ?></td>
                <td><?= e((string)$row['title_fr']) ?></td>
                <td><?= e((string)$row['item_type']) ?></td>
                <td><?= e((string)($row['location']??'—')) ?></td>
                <td><?= $row['is_active'] ? '✓' : '✗' ?></td>
                <td>
                    <div class="table-actions">
                        <a class="btn btn-sm" href="?id=<?= e((string)$row['id']) ?>">Modifier</a>
                        <form method="post" onsubmit="return confirm('Supprimer cet élément ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete_item">
                            <input type="hidden" name="id" value="<?= e((string)$row['id']) ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="8">Aucun élément de programme.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
