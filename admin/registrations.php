<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$typeOptions = ['participant', 'partner', 'speaker', 'sponsor'];
$typeFilter = strtolower((string) ($_GET['type'] ?? ''));
if (!in_array($typeFilter, $typeOptions, true)) {
    $typeFilter = '';
}

$whereSql = '';
$params = [];
if ($typeFilter !== '') {
    $whereSql = ' WHERE participation_type = :type';
    $params['type'] = $typeFilter;
}

$sql = 'SELECT id, first_name, last_name, country, email, phone, organization, participation_type, language, created_at
        FROM registrations' . $whereSql . ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$export = strtolower((string) ($_GET['export'] ?? ''));
if ($export === 'csv' || $export === 'excel') {
    $filenameSuffix = $typeFilter !== '' ? '-' . $typeFilter : '';
    $fileBase = 'registrations' . $filenameSuffix . '-' . date('Ymd-His');

    $headers = ['ID', 'First Name', 'Last Name', 'Country', 'Email', 'Phone', 'Organization', 'Type', 'Lang', 'Created At'];
    $dataRows = [];
    foreach ($rows as $row) {
        $dataRows[] = [
            (string) $row['id'],
            (string) $row['first_name'],
            (string) $row['last_name'],
            (string) $row['country'],
            (string) $row['email'],
            (string) $row['phone'],
            (string) ($row['organization'] ?? ''),
            (string) $row['participation_type'],
            (string) $row['language'],
            (string) $row['created_at'],
        ];
    }

    if ($export === 'csv') {
        output_csv_download($fileBase . '.csv', $headers, $dataRows);
    }

    if (!class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
        output_csv_download($fileBase . '.csv', $headers, $dataRows);
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray($headers, null, 'A1');
    $sheet->fromArray($dataRows, null, 'A2');

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename=' . $fileBase . '.xlsx');
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

$adminTitle = t('admin.menu_registrations');
$activeAdmin = 'registrations';
require __DIR__ . '/_header.php';
?>

<section class="card">
    <form method="get" class="row">
        <div>
            <label for="type"><?= e(t('admin.filter')) ?></label>
            <select name="type" id="type">
                <option value="">Tous</option>
                <?php foreach ($typeOptions as $type): ?>
                    <option value="<?= e($type) ?>" <?= $typeFilter === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:0.5rem;align-items:flex-end;">
            <button type="submit"><?= e(t('admin.filter')) ?></button>
            <a class="btn" href="<?= e(admin_url('registrations.php?type=' . urlencode($typeFilter) . '&export=csv')) ?>"><?= e(t('admin.export_csv')) ?></a>
            <a class="btn btn-muted" href="<?= e(admin_url('registrations.php?type=' . urlencode($typeFilter) . '&export=excel')) ?>"><?= e(t('admin.export_excel')) ?></a>
        </div>
    </form>
</section>

<section class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Email</th>
                <th>Pays</th>
                <th>Type</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e((string) $row['id']) ?></td>
                <td><?= e((string) $row['first_name'] . ' ' . (string) $row['last_name']) ?></td>
                <td><?= e((string) $row['email']) ?></td>
                <td><?= e((string) $row['country']) ?></td>
                <td><?= e((string) $row['participation_type']) ?></td>
                <td><?= e((string) $row['created_at']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
