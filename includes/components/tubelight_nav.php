<?php

declare(strict_types=1);

if (!function_exists('tubelight_icon_svg')) {
    function tubelight_icon_svg(string $icon): string
    {
        $icons = [
            'home'         => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3.75 10.25L12 3.75l8.25 6.5v9a1 1 0 0 1-1 1h-4.5v-6h-5.5v6h-4.5a1 1 0 0 1-1-1v-9Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'about'        => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.8"/><path d="M12 10.3v5.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="7.5" r="1" fill="currentColor"/></svg>',
            'program'      => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.75" y="5.25" width="16.5" height="15" rx="2.4" stroke="currentColor" stroke-width="1.8"/><path d="M8 3.75v3M16 3.75v3M3.75 9.75h16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
            'registration' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12.25a4.25 4.25 0 1 0 0-8.5 4.25 4.25 0 0 0 0 8.5Z" stroke="currentColor" stroke-width="1.8"/><path d="M4.5 20.25c1.35-3.15 4.05-5 7.5-5s6.15 1.85 7.5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
            'contribute'   => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4.75v14.5M7.5 9.25H13a2.75 2.75 0 1 1 0 5.5H9a2.75 2.75 0 1 1 0-5.5h7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'partners'     => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 12.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5ZM16 12.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="1.8"/><path d="M3.75 20.25c.8-2.7 2.75-4.25 5.25-4.25s4.45 1.55 5.25 4.25M10.75 20.25c.7-2.35 2.35-3.75 4.5-3.75s3.8 1.4 4.5 3.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
            'contact'      => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.75" y="5.25" width="16.5" height="13.5" rx="2.25" stroke="currentColor" stroke-width="1.8"/><path d="M4.5 6l7.05 6.05a.7.7 0 0 0 .9 0L19.5 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'fallback'     => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="7.75" stroke="currentColor" stroke-width="1.8"/></svg>',
        ];

        return $icons[$icon] ?? $icons['fallback'];
    }
}

if (!function_exists('render_tubelight_navbar')) {
    function render_tubelight_navbar(array $items, string $activeFile, array $options = []): void
    {
        $brandHref   = (string) ($options['brand_href']  ?? '/');
        $brandLabel  = (string) ($options['brand_label'] ?? t('site.short_name'));
        $brandLogo   = (string) ($options['brand_logo']  ?? base_url('assets/images/logo.jpeg'));
        $showLang    = (bool)   ($options['show_lang']   ?? true);
        $currentPath = isset($options['current_path']) ? (string) $options['current_path'] : null;
        ?>
        <header class="site-header">
            <div class="tubelight-shell">
                <!-- Brand -->
                <a class="tube-brand" href="<?= e($brandHref) ?>" aria-label="<?= e($brandLabel) ?>">
                    <img src="<?= e($brandLogo) ?>" alt="Logo" width="38" height="38">
                    <span><?= e($brandLabel) ?></span>
                </a>

                <!-- Desktop nav -->
                <nav class="tubelight-nav" aria-label="Primary navigation" data-tubelight-nav>
                    <span class="tube-indicator" aria-hidden="true" data-tube-indicator></span>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $file  = (string) ($item['file'] ?? 'index.php');
                        $url   = (string) ($item['url']  ?? base_url($file));
                        $label = (string) ($item['label'] ?? $file);
                        $icon  = (string) ($item['icon']  ?? 'fallback');
                        $isCta = !empty($item['cta']);

                        if ($currentPath !== null) {
                            $navPath  = ($url === '/') ? '/' : rtrim($url, '/');
                            $isActive = $currentPath === $navPath;
                        } else {
                            $isActive = $activeFile === $file;
                        }
                        ?>
                        <a href="<?= e($url) ?>"
                           class="tube-link<?= $isActive ? ' active' : '' ?><?= $isCta ? ' tube-link--cta' : '' ?>"
                           data-tube-link
                           aria-label="<?= e($label) ?>">
                            <span class="tube-icon" aria-hidden="true"><?= tubelight_icon_svg($icon) ?></span>
                            <span class="tube-label"><?= e($label) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <!-- Desktop language switcher -->
                <?php if ($showLang): ?>
                    <div class="tube-lang" aria-label="Language switcher">
                        <a href="<?= e(lang_url('fr')) ?>" class="<?= current_lang() === 'fr' ? 'active' : '' ?>">FR</a>
                        <a href="<?= e(lang_url('de')) ?>" class="<?= current_lang() === 'de' ? 'active' : '' ?>">DE</a>
                    </div>
                <?php endif; ?>

            </div>
        </header>

        <!-- Mobile hamburger — hors du header pour éviter pointer-events:none -->
        <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Ouvrir le menu" aria-expanded="false" aria-controls="mobileSidebar">
            <span class="mobile-menu-bar"></span>
            <span class="mobile-menu-bar"></span>
            <span class="mobile-menu-bar"></span>
        </button>

        <!-- Sidebar overlay -->
        <div class="mobile-sidebar-overlay" id="mobileSidebarOverlay" aria-hidden="true"></div>

        <!-- Mobile sidebar -->
        <aside class="mobile-sidebar" id="mobileSidebar" aria-label="Navigation mobile" aria-hidden="true">
            <div class="mobile-sidebar-header">
                <a class="mobile-sidebar-brand" href="<?= e($brandHref) ?>">
                    <img src="<?= e($brandLogo) ?>" alt="Logo" width="40" height="40">
                    <span><?= e($brandLabel) ?></span>
                </a>
                <button class="mobile-sidebar-close" id="mobileSidebarClose" aria-label="Fermer le menu">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
            </div>

            <nav class="mobile-sidebar-nav">
                <?php foreach ($items as $item): ?>
                    <?php
                    $file  = (string) ($item['file'] ?? 'index.php');
                    $url   = (string) ($item['url']  ?? base_url($file));
                    $label = (string) ($item['label'] ?? $file);
                    $icon  = (string) ($item['icon']  ?? 'fallback');
                    $isCta = !empty($item['cta']);

                    if ($currentPath !== null) {
                        $navPath  = ($url === '/') ? '/' : rtrim($url, '/');
                        $isActive = $currentPath === $navPath;
                    } else {
                        $isActive = $activeFile === $file;
                    }
                    ?>
                    <a href="<?= e($url) ?>"
                       class="mobile-sidebar-link<?= $isActive ? ' is-active' : '' ?><?= $isCta ? ' is-cta' : '' ?>">
                        <span class="mobile-sidebar-icon"><?= tubelight_icon_svg($icon) ?></span>
                        <span><?= e($label) ?></span>
                        <?php if ($isActive): ?>
                            <span class="mobile-sidebar-active-dot" aria-hidden="true"></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php if ($showLang): ?>
                <div class="mobile-sidebar-lang">
                    <span class="mobile-sidebar-lang-label">Langue / Sprache</span>
                    <div class="mobile-sidebar-lang-btns">
                        <a href="<?= e(lang_url('fr')) ?>" class="mobile-sidebar-lang-btn<?= current_lang() === 'fr' ? ' is-active' : '' ?>">
                            <span>🇫🇷</span> Français
                        </a>
                        <a href="<?= e(lang_url('de')) ?>" class="mobile-sidebar-lang-btn<?= current_lang() === 'de' ? ' is-active' : '' ?>">
                            <span>🇩🇪</span> Deutsch
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mobile-sidebar-footer">
                <p>© <?= date('Y') ?> ugfa-ev.org</p>
            </div>
        </aside>

        <script>
        (function () {
            var btn     = document.getElementById('mobileMenuBtn');
            var sidebar = document.getElementById('mobileSidebar');
            var overlay = document.getElementById('mobileSidebarOverlay');
            var closeBtn = document.getElementById('mobileSidebarClose');

            if (!btn || !sidebar || !overlay) return;

            function openSidebar() {
                sidebar.classList.add('is-open');
                overlay.classList.add('is-open');
                sidebar.setAttribute('aria-hidden', 'false');
                btn.setAttribute('aria-expanded', 'true');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.remove('is-open');
                overlay.classList.remove('is-open');
                sidebar.setAttribute('aria-hidden', 'true');
                btn.setAttribute('aria-expanded', 'false');
                document.body.style.overflow = '';
            }

            btn.addEventListener('click', openSidebar);
            overlay.addEventListener('click', closeSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeSidebar();
            });

            sidebar.querySelectorAll('.mobile-sidebar-link').forEach(function (link) {
                link.addEventListener('click', closeSidebar);
            });
        })();
        </script>
        <?php
    }
}
