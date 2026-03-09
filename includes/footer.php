<?php

declare(strict_types=1);
?>
</main>
<footer class="site-footer">
    <div class="container footer-main">
        <div class="footer-brand-col">
            <a class="footer-brand" href="<?= e(base_url('index.php')) ?>">
                <img src="<?= e(base_url('assets/images/logo.svg')) ?>" alt="Logo Guinee Dortmund 2026" width="46" height="46">
                <span><?= e(t('site.short_name')) ?></span>
            </a>
            <p class="footer-tagline"><?= e(t('footer.event_title')) ?></p>
            <p class="footer-meta"><?= e(t('footer.event_dates')) ?></p>
            <p class="footer-meta"><?= e(t('footer.event_location')) ?></p>
        </div>

        <nav class="footer-link-col" aria-label="<?= e(t('footer.quick_links')) ?>">
            <h3><?= e(t('footer.quick_links')) ?></h3>
            <ul>
                <li><a href="<?= e(base_url('index.php')) ?>"><?= e(t('nav.home')) ?></a></li>
                <li><a href="<?= e(base_url('about.php')) ?>"><?= e(t('nav.about')) ?></a></li>
                <li><a href="<?= e(base_url('program.php')) ?>"><?= e(t('nav.program')) ?></a></li>
                <li><a href="<?= e(base_url('contact.php')) ?>"><?= e(t('nav.contact')) ?></a></li>
            </ul>
        </nav>

        <nav class="footer-link-col" aria-label="<?= e(t('nav.registration')) ?>">
            <h3><?= e(t('nav.registration')) ?></h3>
            <ul>
                <li><a href="<?= e(base_url('registration.php')) ?>"><?= e(t('nav.registration')) ?></a></li>
                <li><a href="<?= e(base_url('contribute.php')) ?>"><?= e(t('nav.contribute')) ?></a></li>
                <li><a href="<?= e(base_url('partners.php')) ?>"><?= e(t('nav.partners')) ?></a></li>
            </ul>
        </nav>

        <nav class="footer-link-col" aria-label="<?= e(t('footer.legal')) ?>">
            <h3><?= e(t('footer.legal')) ?></h3>
            <ul>
                <li><a href="<?= e(base_url('privacy.php')) ?>"><?= e(t('footer.privacy')) ?></a></li>
                <li><a href="<?= e(base_url('impressum.php')) ?>"><?= e(t('footer.impressum')) ?></a></li>
                <li><a href="<?= e(base_url('sitemap.xml')) ?>">Sitemap</a></li>
            </ul>
        </nav>

        <nav class="footer-link-col" aria-label="Resources">
            <h3>Resources</h3>
            <ul>
                <li><a href="<?= e(base_url('program.php')) ?>"><?= e(t('nav.program')) ?></a></li>
                <li><a href="<?= e(base_url('partners.php')) ?>"><?= e(t('nav.partners')) ?></a></li>
                <li><a href="<?= e(base_url('contact.php')) ?>"><?= e(t('nav.contact')) ?></a></li>
            </ul>
        </nav>
    </div>

    <div class="container footer-bottom">
        <div class="footer-legal">
            <small>&copy; <?= date('Y') ?> guineedortmund2026.org - <?= e(t('footer.rights')) ?></small>
            <p class="footer-credit">Développé par Mimi Sagno, développeur web et application.</p>
            <div class="footer-policy-links">
                <a href="<?= e(base_url('privacy.php')) ?>"><?= e(t('footer.privacy')) ?></a>
                <a href="<?= e(base_url('impressum.php')) ?>"><?= e(t('footer.impressum')) ?></a>
            </div>
        </div>

        <div class="footer-social" aria-label="Social media">
            <a href="https://linkedin.com/company/guineedortmund2026" target="_blank" rel="noopener" aria-label="LinkedIn">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M6.8 8.7V19M6.8 5.9a1.35 1.35 0 1 0 0 2.7 1.35 1.35 0 0 0 0-2.7ZM11.3 19v-5.7c0-1.7 1.1-2.9 2.6-2.9s2.3 1.1 2.3 2.8V19M11.3 11V8.7" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a href="https://x.com/guinee_dortmund" target="_blank" rel="noopener" aria-label="X">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4.5 5h4.2l10.8 14h-4.2L4.5 5Zm7.1 8L4.8 19M19.2 5l-6.6 6.9" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
            <a href="https://facebook.com/guineedortmund2026" target="_blank" rel="noopener" aria-label="Facebook">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14.1 19v-6.6h2.2l.3-2.5h-2.5V8.2c0-.7.2-1.2 1.3-1.2h1.3V4.8a17.4 17.4 0 0 0-1.9-.1c-1.9 0-3.1 1.2-3.1 3.4V10H9.5v2.5h2.1V19h2.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
            </a>
            <a href="https://youtube.com" target="_blank" rel="noopener" aria-label="YouTube">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.9" y="6.6" width="16.2" height="10.8" rx="2.7" stroke="currentColor" stroke-width="1.9"/><path d="M10.3 9.6 15 12l-4.7 2.4V9.6Z" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round"/></svg>
            </a>
        </div>
    </div>
</footer>
<button class="scroll-top-btn" type="button" aria-label="Nach oben" data-scroll-top>
    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
        <path d="M12 19V5M12 5l-6 6M12 5l6 6" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</button>
<script>
window.GD2026_EVENT_START = <?= json_encode(site_event_start_iso(isset($pdo) && $pdo instanceof PDO ? $pdo : null), JSON_UNESCAPED_SLASHES) ?>;
window.GD2026_VALIDATE_PREFIX = <?= json_encode(t('validation.client_prefix'), JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js" defer></script>
<script src="<?= e(base_url('assets/js/main.js')) ?>" defer></script>
</body>
</html>
