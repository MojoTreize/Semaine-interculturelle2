<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$itemTypes = ['conference', 'panel', 'exhibition', 'networking', 'ceremony', 'workshop'];

if (is_post()) {
    verify_csrf_or_fail();
    $action = post_string('action');

    if ($action === 'add_item') {
        $eventDate = post_string('event_date');
        $startTime = post_string('start_time');
        $endTime = post_string('end_time');
        $titleFr = post_string('title_fr');
        $titleDe = post_string('title_de');
        $descriptionFr = post_string('description_fr');
        $descriptionDe = post_string('description_de');
        $location = post_string('location');
        $itemType = post_string('item_type');
        $displayOrder = (int) post_string('display_order');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($eventDate === '' || $titleFr === '' || $titleDe === '' || !in_array($itemType, $itemTypes, true)) {
            set_flash('error', t('validation.required'));
            redirect('admin/program.php');
        }

        $stmt = $pdo->prepare('INSERT INTO program_items
            (event_date, start_time, end_time, title_fr, title_de, description_fr, description_de, location, item_type, display_order, is_active)
            VALUES
            (:event_date, :start_time, :end_time, :title_fr, :title_de, :description_fr, :description_de, :location, :item_type, :display_order, :is_active)');
        $stmt->execute([
            'event_date' => $eventDate,
            'start_time' => $startTime !== '' ? $startTime : null,
            'end_time' => $endTime !== '' ? $endTime : null,
            'title_fr' => $titleFr,
            'title_de' => $titleDe,
            'description_fr' => $descriptionFr !== '' ? $descriptionFr : null,
            'description_de' => $descriptionDe !== '' ? $descriptionDe : null,
            'location' => $location !== '' ? $location : null,
            'item_type' => $itemType,
            'display_order' => $displayOrder,
            'is_active' => $isActive,
        ]);

        set_flash('success', 'Element du programme ajoute.');
        redirect('admin/program.php');
    }

    if ($action === 'delete_item') {
        $id = (int) post_string('id');
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM program_items WHERE id = :id');
            $stmt->execute(['id' => $id]);
            set_flash('success', 'Element supprime.');
        }
        redirect('admin/program.php');
    }
}

$rows = $pdo->query('SELECT id, event_date, start_time, end_time, title_fr, title_de, location, item_type, is_active, display_order
                     FROM program_items ORDER BY event_date ASC, start_time ASC, display_order ASC')->fetchAll();

$adminTitle = t('admin.menu_program');
$activeAdmin = 'program';
require __DIR__ . '/_header.php';
?>

<section class="card">
    <h3>Ajouter un element de programme</h3>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_item">

        <div class="row">
            <div>
                <label>Date</label>
                <input type="date" name="event_date" required>
            </div>
            <div>
                <label>Type</label>
                <select name="item_type">
                    <?php foreach ($itemTypes as $type): ?>
                        <option value="<?= e($type) ?>"><?= e($type) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div>
                <label>Heure debut</label>
                <input type="time" name="start_time">
            </div>
            <div>
                <label>Heure fin</label>
                <input type="time" name="end_time">
            </div>
        </div>

        <div class="row">
            <div>
                <label>Titre FR</label>
                <input type="text" name="title_fr" required>
            </div>
            <div>
                <label>Titre DE</label>
                <input type="text" name="title_de" required>
            </div>
        </div>

        <div class="row">
            <div>
                <label>Description FR</label>
                <textarea name="description_fr"></textarea>
            </div>
            <div>
                <label>Description DE</label>
                <textarea name="description_de"></textarea>
            </div>
        </div>

        <div class="row">
            <div>
                <label>Lieu</label>
                <input type="text" name="location">
            </div>
            <div>
                <label>Ordre</label>
                <input type="number" name="display_order" value="0">
            </div>
        </div>

        <p><label><input type="checkbox" name="is_active" value="1" checked> Actif</label></p>
        <button type="submit"><?= e(t('buttons.add')) ?></button>
    </form>
</section>

<section class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Horaire</th>
                <th>Titre FR</th>
                <th>Type</th>
                <th>Actif</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e((string) $row['id']) ?></td>
                <td><?= e((string) $row['event_date']) ?></td>
                <td><?= e(substr((string) ($row['start_time'] ?? ''), 0, 5)) ?> - <?= e(substr((string) ($row['end_time'] ?? ''), 0, 5)) ?></td>
                <td><?= e((string) $row['title_fr']) ?></td>
                <td><?= e((string) $row['item_type']) ?></td>
                <td><?= e((string) $row['is_active']) ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Supprimer cet element ?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_item">
                        <input type="hidden" name="id" value="<?= e((string) $row['id']) ?>">
                        <button type="submit">Supprimer</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
