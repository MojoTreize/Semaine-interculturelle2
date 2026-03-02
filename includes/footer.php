<?php

declare(strict_types=1);
?>
</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <h3><?= e(t('footer.event_title')) ?></h3>
            <p><?= e(t('footer.event_dates')) ?></p>
            <p><?= e(t('footer.event_location')) ?></p>
        </div>
        <div>
            <h3><?= e(t('footer.quick_links')) ?></h3>
            <ul>
                <li><a href="<?= e(base_url('registration.php')) ?>"><?= e(t('nav.registration')) ?></a></li>
                <li><a href="<?= e(base_url('contribute.php')) ?>"><?= e(t('nav.contribute')) ?></a></li>
                <li><a href="<?= e(base_url('partners.php')) ?>"><?= e(t('nav.partners')) ?></a></li>
                <li><a href="<?= e(base_url('contact.php')) ?>"><?= e(t('nav.contact')) ?></a></li>
            </ul>
        </div>
        <div>
            <h3><?= e(t('footer.legal')) ?></h3>
            <ul>
                <li><a href="<?= e(base_url('privacy.php')) ?>"><?= e(t('footer.privacy')) ?></a></li>
                <li><a href="<?= e(base_url('impressum.php')) ?>"><?= e(t('footer.impressum')) ?></a></li>
                <li><a href="<?= e(admin_url('login.php')) ?>"><?= e(t('footer.admin')) ?></a></li>
            </ul>
        </div>
    </div>
    <div class="container footer-bottom">
        <small>&copy; <?= date('Y') ?> guineedortmund2026.org - <?= e(t('footer.rights')) ?></small>
    </div>
</footer>
<script>
window.GD2026_EVENT_START = <?= json_encode(site_event_start_iso(isset($pdo) && $pdo instanceof PDO ? $pdo : null), JSON_UNESCAPED_SLASHES) ?>;
window.GD2026_VALIDATE_PREFIX = <?= json_encode(t('validation.client_prefix'), JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="<?= e(base_url('assets/js/main.js')) ?>" defer></script>
</body>
</html>
