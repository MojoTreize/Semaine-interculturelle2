<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$partnerTypes = ['partner', 'sponsor', 'institutional'];

if (is_post()) {
    verify_csrf_or_fail();
    $action = post_string('action');

    if ($action === 'add_partner') {
        $name = post_string('name');
        $websiteUrl = post_string('website_url');
        $partnerType = post_string('partner_type');
        $displayOrder = (int) post_string('display_order');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || !in_array($partnerType, $partnerTypes, true)) {
            set_flash('error', t('validation.required'));
            redirect('admin/partners.php');
        }

        $stmt = $pdo->prepare('INSERT INTO partners (name, website_url, partner_type, display_order, is_active)
                               VALUES (:name, :website_url, :partner_type, :display_order, :is_active)');
        $stmt->execute([
            'name' => $name,
            'website_url' => $websiteUrl !== '' ? $websiteUrl : null,
            'partner_type' => $partnerType,
            'display_order' => $displayOrder,
            'is_active' => $isActive,
        ]);

        set_flash('success', 'Partenaire ajoute.');
        redirect('admin/partners.php');
    }

    if ($action === 'delete_partner') {
        $id = (int) post_string('id');
        if ($id > 0) {
            $stmt = $pdo->prepare('DELETE FROM partners WHERE id = :id');
            $stmt->execute(['id' => $id]);
            set_flash('success', 'Partenaire supprime.');
        }
        redirect('admin/partners.php');
    }
}

$rows = $pdo->query('SELECT id, name, website_url, partner_type, display_order, is_active, created_at
                     FROM partners ORDER BY display_order ASC, id ASC')->fetchAll();

$adminTitle = t('admin.menu_partners');
$activeAdmin = 'partners';
require __DIR__ . '/_header.php';
?>

<section class="card">
    <h3>Ajouter un partenaire</h3>
    <form method="post" class="row">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_partner">
        <div>
            <label for="name">Nom</label>
            <input id="name" type="text" name="name" required>
        </div>
        <div>
            <label for="website_url">Site web</label>
            <input id="website_url" type="url" name="website_url">
        </div>
        <div>
            <label for="partner_type">Type</label>
            <select id="partner_type" name="partner_type">
                <?php foreach ($partnerTypes as $type): ?>
                    <option value="<?= e($type) ?>"><?= e($type) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="display_order">Ordre</label>
            <input id="display_order" type="number" name="display_order" value="0">
        </div>
        <div>
            <label><input type="checkbox" name="is_active" value="1" checked> Actif</label>
        </div>
        <div>
            <button type="submit"><?= e(t('buttons.add')) ?></button>
        </div>
    </form>
</section>

<section class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Type</th>
                <th>Actif</th>
                <th>Ordre</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e((string) $row['id']) ?></td>
                <td><?= e((string) $row['name']) ?></td>
                <td><?= e((string) $row['partner_type']) ?></td>
                <td><?= e((string) $row['is_active']) ?></td>
                <td><?= e((string) $row['display_order']) ?></td>
                <td>
                    <form method="post" onsubmit="return confirm('Supprimer ce partenaire ?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete_partner">
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
