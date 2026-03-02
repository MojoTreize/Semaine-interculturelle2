<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$stmt = $pdo->query('SELECT id, organization_name, contact_person, email, phone, website, sponsorship_level, message, created_at
                     FROM sponsor_requests ORDER BY created_at DESC');
$rows = $stmt->fetchAll();

$adminTitle = t('admin.menu_sponsors');
$activeAdmin = 'sponsors';
require __DIR__ . '/_header.php';
?>

<section class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Organisation</th>
                <th>Contact</th>
                <th>Email</th>
                <th>Niveau</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e((string) $row['id']) ?></td>
                <td><?= e((string) $row['organization_name']) ?></td>
                <td><?= e((string) $row['contact_person']) ?></td>
                <td><?= e((string) $row['email']) ?></td>
                <td><?= e((string) $row['sponsorship_level']) ?></td>
                <td><?= e((string) $row['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
