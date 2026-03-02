<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$stmt = $pdo->query('SELECT id, full_name, email, subject, message, language, created_at
                     FROM contact_messages ORDER BY created_at DESC');
$rows = $stmt->fetchAll();

$adminTitle = t('admin.menu_contacts');
$activeAdmin = 'contacts';
require __DIR__ . '/_header.php';
?>

<section class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Objet</th>
                <th>Message</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e((string) $row['id']) ?></td>
                <td><?= e((string) $row['full_name']) ?></td>
                <td><?= e((string) $row['email']) ?></td>
                <td><?= e((string) $row['subject']) ?></td>
                <?php
                    $preview = (string) $row['message'];
                    if (strlen($preview) > 90) {
                        $preview = substr($preview, 0, 90) . '...';
                    }
                ?>
                <td><?= e($preview) ?></td>
                <td><?= e((string) $row['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
