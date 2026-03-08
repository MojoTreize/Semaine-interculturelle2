<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$stats = [
    'registrations' => 0,
    'donations' => 0,
    'donations_paid' => 0.0,
    'donations_pending' => 0,
    'sponsors' => 0,
    'contacts' => 0,
    'partners_active' => 0,
    'program_items' => 0,
];

$recentActivity = [];

try {
    $stats['registrations'] = (int) $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
    $stats['donations'] = (int) $pdo->query('SELECT COUNT(*) FROM donations')->fetchColumn();
    $stats['donations_paid'] = (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE payment_status = 'paid'")->fetchColumn();
    $stats['donations_pending'] = (int) $pdo->query("SELECT COUNT(*) FROM donations WHERE payment_status = 'pending'")->fetchColumn();
    $stats['sponsors'] = (int) $pdo->query('SELECT COUNT(*) FROM sponsor_requests')->fetchColumn();
    $stats['contacts'] = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
    $stats['partners_active'] = (int) $pdo->query('SELECT COUNT(*) FROM partners WHERE is_active = 1')->fetchColumn();
    $stats['program_items'] = (int) $pdo->query('SELECT COUNT(*) FROM program_items WHERE is_active = 1')->fetchColumn();

    $registrationsRaw = $pdo->query("SELECT first_name, last_name, created_at FROM registrations ORDER BY created_at DESC LIMIT 5")->fetchAll();
    $registrations = array_map(static function (array $row): array {
        return [
            'source' => 'registration',
            'label' => trim((string) ($row['first_name'] ?? '') . ' ' . (string) ($row['last_name'] ?? '')),
            'created_at' => (string) ($row['created_at'] ?? ''),
        ];
    }, $registrationsRaw);
    $donations = $pdo->query("SELECT 'donation' AS source, COALESCE(donor_name, donor_email, 'Don anonyme') AS label, created_at FROM donations ORDER BY created_at DESC LIMIT 5")->fetchAll();
    $sponsors = $pdo->query("SELECT 'sponsor' AS source, organization_name AS label, created_at FROM sponsor_requests ORDER BY created_at DESC LIMIT 5")->fetchAll();
    $contacts = $pdo->query("SELECT 'contact' AS source, full_name AS label, created_at FROM contact_messages ORDER BY created_at DESC LIMIT 5")->fetchAll();

    $recentActivity = array_merge($registrations, $donations, $sponsors, $contacts);
    usort($recentActivity, static function (array $a, array $b): int {
        return strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? ''));
    });
    $recentActivity = array_slice($recentActivity, 0, 12);
} catch (Throwable) {
    // Keep defaults if a query fails.
}

$adminTitle = t('admin.dashboard');
$activeAdmin = 'dashboard';
require __DIR__ . '/_header.php';
?>

<section class="dashboard-hero card">
    <div>
        <h3>Vue d'ensemble</h3>
        <p class="hint">Suivi en temps reel des inscriptions, dons et interactions.</p>
    </div>
    <div class="dashboard-hero-meta">
        <span class="hero-chip">Reg: <?= e((string) $stats['registrations']) ?></span>
        <span class="hero-chip">Dons: <?= e((string) $stats['donations']) ?></span>
        <span class="hero-chip">Contacts: <?= e((string) $stats['contacts']) ?></span>
    </div>
</section>

<section class="stats dashboard-stats">
    <article class="stat stat--accent">
        <span>Inscriptions</span>
        <strong><?= e((string) $stats['registrations']) ?></strong>
    </article>
    <article class="stat stat--blue">
        <span>Contributions (nb)</span>
        <strong><?= e((string) $stats['donations']) ?></strong>
    </article>
    <article class="stat stat--green">
        <span>Total collecte</span>
        <strong><?= e(format_amount((float) $stats['donations_paid'])) ?></strong>
    </article>
    <article class="stat stat--violet">
        <span>Sponsors / Contacts</span>
        <strong><?= e((string) $stats['sponsors']) ?> / <?= e((string) $stats['contacts']) ?></strong>
    </article>
    <article class="stat stat--amber">
        <span>Dons en attente</span>
        <strong><?= e((string) $stats['donations_pending']) ?></strong>
    </article>
    <article class="stat">
        <span>Partenaires actifs</span>
        <strong><?= e((string) $stats['partners_active']) ?></strong>
    </article>
    <article class="stat">
        <span>Programme actif</span>
        <strong><?= e((string) $stats['program_items']) ?></strong>
    </article>
</section>

<section class="card dashboard-actions">
    <h3>Actions rapides</h3>
    <div class="quick-actions-grid">
        <a class="btn" href="<?= e(admin_url('registrations.php')) ?>"><?= e(t('admin.menu_registrations')) ?></a>
        <a class="btn" href="<?= e(admin_url('donations.php')) ?>"><?= e(t('admin.menu_donations')) ?></a>
        <a class="btn" href="<?= e(admin_url('program.php')) ?>"><?= e(t('admin.menu_program')) ?></a>
        <a class="btn btn-muted" href="<?= e(admin_url('contacts.php')) ?>"><?= e(t('admin.menu_contacts')) ?></a>
    </div>
</section>

<section class="card dashboard-activity">
    <h3>Activite recente</h3>
    <table>
        <thead>
            <tr>
                <th>Type</th>
                <th>Detail</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($recentActivity)): ?>
            <tr>
                <td colspan="3">Aucune activite pour le moment.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($recentActivity as $item): ?>
                <?php
                $source = (string) ($item['source'] ?? '');
                $typeLabel = match ($source) {
                    'registration' => 'Inscription',
                    'donation' => 'Don',
                    'sponsor' => 'Sponsor',
                    'contact' => 'Contact',
                    default => 'Activite',
                };
                ?>
                <tr>
                    <td><span class="activity-badge activity-badge--<?= e($source !== '' ? $source : 'default') ?>"><?= e($typeLabel) ?></span></td>
                    <td><?= e((string) ($item['label'] ?? '-')) ?></td>
                    <td><?= e((string) ($item['created_at'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
