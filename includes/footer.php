<?php

declare(strict_types=1);
?>
</main>
<footer class="site-footer">
    <div class="container footer-main">
        <div class="footer-brand-col">
            <a class="footer-brand" href="<?= e(base_url('index.php')) ?>">
                <img src="<?= e(base_url('assets/images/logo.jpeg')) ?>" alt="Logo Union de la Guinee Forestière en Allemagne" width="46" height="46">
                <span><?= e(t('site.short_name')) ?></span>
            </a>
            <p class="footer-tagline"><?= e(t('footer.event_title')) ?></p>
            <div class="footer-social" aria-label="Social media">
                <a href="https://www.facebook.com/profile.php?id=61591357127241" target="_blank" rel="noopener" aria-label="Facebook">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M14.1 19v-6.6h2.2l.3-2.5h-2.5V8.2c0-.7.2-1.2 1.3-1.2h1.3V4.8a17.4 17.4 0 0 0-1.9-.1c-1.9 0-3.1 1.2-3.1 3.4V10H9.5v2.5h2.1V19h2.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                </a>
            </div>
        </div>

        <nav class="footer-link-col" aria-label="<?= e(t('footer.quick_links')) ?>">
            <h3><?= e(t('footer.quick_links')) ?></h3>
            <ul>
                <li><a href="<?= e(base_url('index.php')) ?>"><?= e(t('nav.home')) ?></a></li>
                <li><a href="<?= e(base_url('about.php')) ?>"><?= e(t('nav.about')) ?></a></li>
                <li><a href="<?= e(base_url('program.php')) ?>"><?= e(t('nav.program')) ?></a></li>
                <li><a href="<?= e(base_url('registration.php')) ?>"><?= e(t('nav.registration')) ?></a></li>
                <li><a href="<?= e(base_url('contribute.php')) ?>"><?= e(t('nav.contribute')) ?></a></li>
                <li><a href="<?= e(base_url('partners.php')) ?>"><?= e(t('nav.partners')) ?></a></li>
                <li><a href="<?= e(base_url('contact.php')) ?>"><?= e(t('nav.contact')) ?></a></li>
            </ul>
        </nav>

        <div class="footer-link-col footer-contact-col">
            <h3><?= e(t('footer.contact_title')) ?></h3>
            <ul class="footer-contact-list">
                <li>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 21s7-6.1 7-11a7 7 0 10-14 0c0 4.9 7 11 7 11z" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="10" r="2.3" stroke="currentColor" stroke-width="1.6"/></svg>
                    <a href="https://www.google.com/maps/search/?api=1&query=<?= rawurlencode(t('footer.event_location')) ?>" target="_blank" rel="noopener noreferrer"><?= e(t('footer.event_location')) ?></a>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 4h3l1.6 4-2 1.4a12 12 0 006 6l1.4-2 4 1.6V19a2 2 0 01-2.2 2A16 16 0 014 6.2 2 2 0 015 4z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                    <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', t('footer.phone'))) ?>"><?= e(t('footer.phone')) ?></a>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="5" width="17" height="14" rx="2.4" stroke="currentColor" stroke-width="1.6"/><path d="M4 7l8 6 8-6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg>
                    <a href="mailto:<?= e(t('footer.email')) ?>"><?= e(t('footer.email')) ?></a>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M3.5 12h17M12 3.5c2.5 2.3 2.5 14.7 0 17M12 3.5c-2.5 2.3-2.5 14.7 0 17" stroke="currentColor" stroke-width="1.6"/></svg>
                    <a href="https://ugfa-ev.org" target="_blank" rel="noopener noreferrer"><?= e(t('footer.website')) ?></a>
                </li>
            </ul>
        </div>

        <div class="footer-link-col footer-newsletter-col">
            <h3><?= e(t('footer.newsletter_title')) ?></h3>
            <p class="footer-newsletter-text"><?= e(t('footer.newsletter_text')) ?></p>
            <form class="footer-newsletter-form" action="<?= e(base_url('contact.php')) ?>" method="get">
                <input type="email" name="newsletter_email" placeholder="<?= e(t('footer.newsletter_placeholder')) ?>" aria-label="<?= e(t('footer.newsletter_placeholder')) ?>" required>
                <button type="submit" aria-label="<?= e(t('footer.newsletter_button')) ?>">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </form>
        </div>
    </div>

    <?php
    try {
        $orgaStmt = $pdo->query("SELECT name, website_url, logo_path, vr_number FROM partners WHERE partner_type='institutional' AND is_active=1 ORDER BY display_order ASC, id ASC");
        $orgaRows = $orgaStmt->fetchAll();
    } catch (Throwable) {
        $orgaRows = [];
    }
    if (!empty($orgaRows)):
    ?>
    <div class="container footer-orga">
        <p class="footer-orga-label">
            <?= current_lang() === 'de' ? 'Veranstaltende Vereine' : 'Associations organisatrices' ?>
        </p>
        <ul class="footer-orga-list">
            <?php foreach ($orgaRows as $orga):
                $orgaHasLogo = !empty($orga['logo_path']) && is_file(ROOT_PATH . '/' . $orga['logo_path']);
                $orgaHasSite = !empty($orga['website_url']);
                $orgaTag     = $orgaHasSite ? 'a' : 'span';
                $orgaAttrs   = $orgaHasSite ? ' href="' . e((string)$orga['website_url']) . '" target="_blank" rel="noopener noreferrer"' : '';
            ?>
            <li>
                <<?= $orgaTag ?> class="footer-orga-item"<?= $orgaAttrs ?>>
                    <?php if ($orgaHasLogo): ?>
                        <img src="<?= e(base_url((string)$orga['logo_path'])) ?>" alt="<?= e((string)$orga['name']) ?>" width="28" height="28" loading="lazy">
                    <?php else: ?>
                        <span class="footer-orga-avatar" aria-hidden="true">
                            <?= e(mb_substr(strip_tags((string)$orga['name']), 0, 1)) ?>
                        </span>
                    <?php endif; ?>
                    <span class="footer-orga-name"><?= e((string)$orga['name']) ?></span>
                    <?php if (!empty($orga['vr_number'])): ?>
                        <span class="footer-orga-vr"><?= e((string)$orga['vr_number']) ?></span>
                    <?php endif; ?>
                </<?= $orgaTag ?>>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="container footer-bottom">
        <div class="footer-legal">
            <small>&copy; <?= date('Y') ?> ugfa-ev.org - <?= e(t('footer.rights')) ?></small>
            <div class="footer-policy-links">
                <a href="<?= e(base_url('privacy.php')) ?>"><?= e(t('footer.privacy')) ?></a>
                <a href="<?= e(base_url('impressum.php')) ?>"><?= e(t('footer.impressum')) ?></a>
            </div>
        </div>

    </div>
</footer>
<?php
$_waNum = trim((string) (isset($pdo) ? get_setting($pdo, 'whatsapp_number', '') : ''));
$_waNum = preg_replace('/[^0-9+]/', '', $_waNum);
$_waHref = $_waNum !== '' ? 'https://wa.me/' . ltrim($_waNum, '+') . '?text=' . rawurlencode('Bonjour, j\'ai besoin d\'aide concernant la Semaine de Coopération Internationale et de Dialogue Interculturelle de la Guinée Forestière en Allemagne.') : '';
?>
<div class="fab-stack">
    <?php if ($_waHref !== ''): ?>
    <a class="fab-btn fab-whatsapp" href="<?= e($_waHref) ?>" target="_blank" rel="noopener noreferrer" aria-label="Contacter via WhatsApp">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.890-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>
    <?php endif; ?>
    <button class="fab-btn fab-scroll-top" type="button" aria-label="Retour en haut" data-scroll-top>
        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 19V5M12 5l-6 6M12 5l6 6" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </button>
</div>
<script>
window.GD2026_EVENT_START = <?= json_encode(site_event_start_iso(isset($pdo) && $pdo instanceof PDO ? $pdo : null), JSON_UNESCAPED_SLASHES) ?>;
window.GD2026_VALIDATE_PREFIX = <?= json_encode(t('validation.client_prefix'), JSON_UNESCAPED_SLASHES) ?>;
</script>
<script src="https://unpkg.com/aos@2.3.4/dist/aos.js" defer></script>
<script src="<?= e(base_url('assets/js/main.js')) ?>" defer></script>
</body>
</html>
