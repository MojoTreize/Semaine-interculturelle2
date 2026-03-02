<?php
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';
admin_require_login();

$stats = [
    'registrations' => 0,
    'donations' => 0,
    'donations_paid' => 0.0,
    'sponsors' => 0,
    'contacts' => 0,
];

try {
    $stats['registrations'] = (int) $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
    $stats['donations'] = (int) $pdo->query('SELECT COUNT(*) FROM donations')->fetchColumn();
    $stats['donations_paid'] = (float) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM donations WHERE payment_status = 'paid'")->fetchColumn();
    $stats['sponsors'] = (int) $pdo->query('SELECT COUNT(*) FROM sponsor_requests')->fetchColumn();
    $stats['contacts'] = (int) $pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn();
} catch (Throwable) {
    // Keep default values.
}

$adminTitle = t('admin.dashboard');
$activeAdmin = 'dashboard';
require __DIR__ . '/_header.php';
?>

<section class="stats">
    <article class="stat">
        <span>Inscriptions</span>
        <strong><?= e((string) $stats['registrations']) ?></strong>
    </article>
    <article class="stat">
        <span>Contributions (nb)</span>
        <strong><?= e((string) $stats['donations']) ?></strong>
    </article>
    <article class="stat">
        <span>Total collecte</span>
        <strong><?= e(format_amount((float) $stats['donations_paid'])) ?></strong>
    </article>
    <article class="stat">
        <span>Sponsors / Contacts</span>
        <strong><?= e((string) $stats['sponsors']) ?> / <?= e((string) $stats['contacts']) ?></strong>
    </article>
</section>

<section class="card">
    <h3>Actions rapides</h3>
    <p><a class="btn" href="<?= e(admin_url('registrations.php')) ?>"><?= e(t('admin.menu_registrations')) ?></a></p>
    <p><a class="btn" href="<?= e(admin_url('donations.php')) ?>"><?= e(t('admin.menu_donations')) ?></a></p>
    <p><a class="btn" href="<?= e(admin_url('program.php')) ?>"><?= e(t('admin.menu_program')) ?></a></p>
</section>

<?php require __DIR__ . '/_footer.php'; ?>
