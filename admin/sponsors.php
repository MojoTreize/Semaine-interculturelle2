<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$levels = [
    'bronze'    => 'Bronze — 100 €',
    'silver'    => 'Silver — 500 €',
    'gold'      => 'Gold — 1 000 €',
    'strategic' => 'Stratégique — 2 000 € et plus',
];

/* ── POST actions ─────────────────────────────────────────────────────────── */
if (is_post()) {
    verify_csrf_or_fail();
    $id     = (int) post_string('id');
    $action = post_string('action');

    if ($action === 'delete' && $id > 0) {
        $pdo->prepare('DELETE FROM sponsor_requests WHERE id = :id')->execute(['id' => $id]);
        set_flash('success', 'Demande supprimée.');
        redirect('admin/sponsors.php');
    }

    if ($action === 'approve' && $id > 0) {
        $stmt = $pdo->prepare('SELECT * FROM sponsor_requests WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $req = $stmt->fetch();
        if ($req) {
            $pdo->prepare('UPDATE sponsor_requests SET status = :s WHERE id = :id')
                ->execute(['s' => 'approved', 'id' => $id]);

            // Insert or update in partners table
            $existing = $pdo->prepare('SELECT id FROM partners WHERE contact_email = :email');
            $existing->execute(['email' => $req['email']]);
            if (!$existing->fetch()) {
                $pdo->prepare('INSERT INTO partners
                    (name, website_url, logo_path, sponsorship_level, contact_email, partner_type, is_active, display_order)
                    VALUES (:name, :website, :logo, :level, :email, :ptype, 1, 0)')
                    ->execute([
                        'name'    => $req['organization_name'],
                        'website' => $req['website'] ?? '',
                        'logo'    => $req['logo_path'] ?? '',
                        'level'   => $req['sponsorship_level'],
                        'email'   => $req['email'],
                        'ptype'   => 'sponsor',
                    ]);
            }
            set_flash('success', 'Sponsor approuvé et ajouté à la liste des partenaires.');
        }
        redirect('admin/sponsors.php');
    }

    if ($action === 'reject' && $id > 0) {
        $pdo->prepare('UPDATE sponsor_requests SET status = :s WHERE id = :id')
            ->execute(['s' => 'rejected', 'id' => $id]);
        set_flash('success', 'Demande rejetée.');
        redirect('admin/sponsors.php');
    }

    if ($action === 'save' && $id > 0) {
        $stmt = $pdo->prepare('UPDATE sponsor_requests
            SET organization_name  = :organization_name,
                contact_person     = :contact_person,
                email              = :email,
                phone              = :phone,
                website            = :website,
                sponsorship_level  = :sponsorship_level
            WHERE id = :id');
        $stmt->execute([
            'organization_name' => post_string('organization_name'),
            'contact_person'    => post_string('contact_person'),
            'email'             => post_string('email'),
            'phone'             => post_string('phone'),
            'website'           => post_string('website'),
            'sponsorship_level' => post_string('sponsorship_level'),
            'id'                => $id,
        ]);
        set_flash('success', 'Demande mise à jour.');
        redirect('admin/sponsors.php');
    }
}

/* ── Edit/Detail mode ─────────────────────────────────────────────────────── */
$editRow = null;
$editId  = (int) ($_GET['id'] ?? 0);
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM sponsor_requests WHERE id = :id');
    $stmt->execute(['id' => $editId]);
    $editRow = $stmt->fetch() ?: null;
}

/* ── List query ───────────────────────────────────────────────────────────── */
$statuses = ['pending', 'approved', 'rejected'];
$search   = trim((string) ($_GET['q'] ?? ''));
$level    = strtolower((string) ($_GET['level'] ?? ''));
$status   = strtolower((string) ($_GET['status'] ?? ''));

if (!in_array($level,  array_keys($levels), true)) { $level = ''; }
if (!in_array($status, $statuses, true))            { $status = ''; }

$where  = [];
$params = [];
if ($level  !== '') { $where[] = 'sponsorship_level = :level';  $params['level']  = $level; }
if ($status !== '') { $where[] = 'status = :status';            $params['status'] = $status; }
if ($search !== '') { $where[] = '(organization_name LIKE :q OR contact_person LIKE :q OR email LIKE :q)'; $params['q'] = '%' . $search . '%'; }

$sql = 'SELECT id, organization_name, contact_person, email, phone, website, sponsorship_level, status, message, logo_path, created_at FROM sponsor_requests';
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY FIELD(status,"pending","approved","rejected"), created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pendingCount = (int) $pdo->query("SELECT COUNT(*) FROM sponsor_requests WHERE status='pending'")->fetchColumn();

$adminTitle  = t('admin.menu_sponsors');
$activeAdmin = 'sponsors';
require __DIR__ . '/_header.php';

$statusLabel = [
    'pending'  => ['label' => 'En attente', 'color' => '#f59e0b'],
    'approved' => ['label' => 'Approuvé',   'color' => '#16a34a'],
    'rejected' => ['label' => 'Rejeté',     'color' => '#dc2626'],
];
?>

<?php if ($pendingCount > 0): ?>
<div class="alert alert-warning" style="background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;padding:.75rem 1rem;margin-bottom:1rem;display:flex;align-items:center;gap:.6rem">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
    <strong><?= $pendingCount ?> demande(s) en attente de validation.</strong>
    <?php if ($status !== 'pending'): ?>
        <a href="?status=pending" style="margin-left:auto;font-size:.85rem;color:#d97706">Voir →</a>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($editRow): ?>
<section class="card edit-panel">
    <h3>Modifier la demande #<?= e((string)$editRow['id']) ?></h3>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="id" value="<?= e((string)$editRow['id']) ?>">
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Organisation</label><input type="text" name="organization_name" value="<?= e((string)$editRow['organization_name']) ?>" required></div>
            <div><label>Contact</label><input type="text" name="contact_person" value="<?= e((string)$editRow['contact_person']) ?>"></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Email</label><input type="email" name="email" value="<?= e((string)$editRow['email']) ?>"></div>
            <div><label>Téléphone</label><input type="text" name="phone" value="<?= e((string)($editRow['phone']??'')) ?>"></div>
        </div>
        <div class="row" style="margin-bottom:.75rem">
            <div><label>Site web</label><input type="url" name="website" value="<?= e((string)($editRow['website']??'')) ?>"></div>
            <div>
                <label>Niveau</label>
                <select name="sponsorship_level">
                    <?php foreach ($levels as $lv => $lbl): ?>
                        <option value="<?= e($lv) ?>" <?= $editRow['sponsorship_level'] === $lv ? 'selected' : '' ?>><?= e($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php if (!empty($editRow['logo_path']) && is_file(ROOT_PATH . '/' . $editRow['logo_path'])): ?>
        <p style="font-weight:700;margin:0 0 .4rem">Logo</p>
        <div style="margin-bottom:.75rem;background:#f7f9ff;border:1px solid #d1dded;border-radius:10px;padding:.75rem;display:inline-block">
            <img src="<?= e(base_url((string)$editRow['logo_path'])) ?>" alt="Logo <?= e((string)$editRow['organization_name']) ?>" style="max-height:80px;max-width:240px;object-fit:contain">
        </div>
        <?php endif; ?>
        <?php if (!empty($editRow['message'])): ?>
        <p style="font-weight:700;margin:0 0 .4rem">Message</p>
        <div class="full-message" style="margin-bottom:.75rem"><?= e((string)$editRow['message']) ?></div>
        <?php endif; ?>
        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
            <button type="submit" class="btn btn-success">Enregistrer</button>
            <a class="btn btn-muted" href="<?= e(admin_url('sponsors.php')) ?>">Annuler</a>
            <form method="post" onsubmit="return confirm('Supprimer cette demande ?')" style="display:inline">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= e((string)$editRow['id']) ?>">
                <button class="btn btn-danger btn-sm" type="submit">Supprimer</button>
            </form>
        </div>
    </form>
</section>
<?php endif; ?>

<section class="card">
    <form method="get" style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:flex-end">
        <div><label>Recherche</label><input type="text" name="q" value="<?= e($search) ?>" placeholder="Organisation, contact…" style="width:200px"></div>
        <div>
            <label>Niveau</label>
            <select name="level">
                <option value="">Tous niveaux</option>
                <?php foreach ($levels as $lv => $lbl): ?>
                    <option value="<?= e($lv) ?>" <?= $level === $lv ? 'selected' : '' ?>><?= e($lbl) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Statut</label>
            <select name="status">
                <option value="">Tous statuts</option>
                <option value="pending"  <?= $status === 'pending'  ? 'selected' : '' ?>>En attente</option>
                <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approuvés</option>
                <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejetés</option>
            </select>
        </div>
        <div style="display:flex;gap:.4rem;align-items:flex-end">
            <button type="submit">Filtrer</button>
            <a class="btn btn-muted" href="<?= e(admin_url('sponsors.php')) ?>">Reset</a>
        </div>
    </form>
</section>

<section class="card">
    <p style="color:#4f617e;font-size:.9rem"><?= count($rows) ?> demande(s)</p>
    <table>
        <thead>
            <tr><th>Logo</th><th>Organisation</th><th>Contact</th><th>Email</th><th>Niveau</th><th>Statut</th><th>Date</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $row): ?>
            <?php
            $st     = (string)($row['status'] ?? 'pending');
            $stInfo = $statusLabel[$st] ?? $statusLabel['pending'];
            ?>
            <tr>
                <td>
                    <?php if (!empty($row['logo_path']) && is_file(ROOT_PATH . '/' . $row['logo_path'])): ?>
                        <img src="<?= e(base_url((string)$row['logo_path'])) ?>" alt="Logo" style="height:36px;max-width:72px;object-fit:contain;border-radius:4px;border:1px solid #e0e7f0;background:#f9fbff;padding:2px">
                    <?php else: ?>
                        <span style="color:#bbb;font-size:.8rem">—</span>
                    <?php endif; ?>
                </td>
                <td><?= e((string)$row['organization_name']) ?></td>
                <td><?= e((string)$row['contact_person']) ?></td>
                <td><?= e((string)$row['email']) ?></td>
                <td>
                    <span style="font-size:.8rem;font-weight:600"><?= e($levels[$row['sponsorship_level']] ?? ucfirst((string)$row['sponsorship_level'])) ?></span>
                </td>
                <td>
                    <span style="display:inline-block;padding:.2rem .55rem;border-radius:999px;font-size:.75rem;font-weight:700;background:<?= e($stInfo['color']) ?>22;color:<?= e($stInfo['color']) ?>">
                        <?= e($stInfo['label']) ?>
                    </span>
                </td>
                <td style="white-space:nowrap;font-size:.82rem"><?= e(substr((string)$row['created_at'], 0, 10)) ?></td>
                <td>
                    <div class="table-actions" style="flex-wrap:wrap;gap:.3rem">
                        <?php if ($st === 'pending'): ?>
                            <form method="post" onsubmit="return confirm('Approuver ce sponsor ?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?= e((string)$row['id']) ?>">
                                <button class="btn btn-sm btn-success" type="submit">✓ Approuver</button>
                            </form>
                            <form method="post" onsubmit="return confirm('Rejeter cette demande ?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="reject">
                                <input type="hidden" name="id" value="<?= e((string)$row['id']) ?>">
                                <button class="btn btn-sm btn-danger" type="submit">✕ Rejeter</button>
                            </form>
                        <?php endif; ?>
                        <?php if ($st === 'rejected'): ?>
                            <form method="post" onsubmit="return confirm('Approuver quand même ce sponsor ?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?= e((string)$row['id']) ?>">
                                <button class="btn btn-sm btn-success" type="submit">✓ Approuver</button>
                            </form>
                        <?php endif; ?>
                        <a class="btn btn-sm" href="?id=<?= e((string)$row['id']) ?>">Modifier</a>
                        <form method="post" onsubmit="return confirm('Supprimer cette demande ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= e((string)$row['id']) ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Supprimer</button>
                        </form>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?><tr><td colspan="8" style="text-align:center;color:#9ca3af">Aucune demande sponsor.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
