<?php

declare(strict_types=1);

if (!function_exists('tubelight_icon_svg')) {
    function tubelight_icon_svg(string $icon): string
    {
        $icons = [
            'home' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M3.75 10.25L12 3.75l8.25 6.5v9a1 1 0 0 1-1 1h-4.5v-6h-5.5v6h-4.5a1 1 0 0 1-1-1v-9Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'about' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="8.25" stroke="currentColor" stroke-width="1.8"/><path d="M12 10.3v5.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/><circle cx="12" cy="7.5" r="1" fill="currentColor"/></svg>',
            'program' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.75" y="5.25" width="16.5" height="15" rx="2.4" stroke="currentColor" stroke-width="1.8"/><path d="M8 3.75v3M16 3.75v3M3.75 9.75h16.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
            'registration' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12.25a4.25 4.25 0 1 0 0-8.5 4.25 4.25 0 0 0 0 8.5Z" stroke="currentColor" stroke-width="1.8"/><path d="M4.5 20.25c1.35-3.15 4.05-5 7.5-5s6.15 1.85 7.5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
            'contribute' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 4.75v14.5M7.5 9.25H13a2.75 2.75 0 1 1 0 5.5H9a2.75 2.75 0 1 1 0-5.5h7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'partners' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M8 12.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5ZM16 12.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5Z" stroke="currentColor" stroke-width="1.8"/><path d="M3.75 20.25c.8-2.7 2.75-4.25 5.25-4.25s4.45 1.55 5.25 4.25M10.75 20.25c.7-2.35 2.35-3.75 4.5-3.75s3.8 1.4 4.5 3.75" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>',
            'contact' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.75" y="5.25" width="16.5" height="13.5" rx="2.25" stroke="currentColor" stroke-width="1.8"/><path d="M4.5 6l7.05 6.05a.7.7 0 0 0 .9 0L19.5 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
            'fallback' => '<svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="7.75" stroke="currentColor" stroke-width="1.8"/></svg>',
        ];

        return $icons[$icon] ?? $icons['fallback'];
    }
}

if (!function_exists('render_tubelight_navbar')) {
    function render_tubelight_navbar(array $items, string $activeFile, array $options = []): void
    {
        $brandHref = (string) ($options['brand_href'] ?? base_url('index.php'));
        $brandLabel = (string) ($options['brand_label'] ?? t('site.short_name'));
        $brandLogo = (string) ($options['brand_logo'] ?? base_url('assets/images/logo.svg'));
        $showLang = (bool) ($options['show_lang'] ?? true);
        ?>
        <header class="site-header">
            <div class="tubelight-shell">
                <a class="tube-brand" href="<?= e($brandHref) ?>" aria-label="<?= e($brandLabel) ?>">
                    <img src="<?= e($brandLogo) ?>" alt="Logo" width="38" height="38">
                    <span><?= e($brandLabel) ?></span>
                </a>

                <nav class="tubelight-nav" aria-label="Primary navigation" data-tubelight-nav>
                    <span class="tube-indicator" aria-hidden="true" data-tube-indicator></span>
                    <?php foreach ($items as $item): ?>
                        <?php
                        $file = (string) ($item['file'] ?? 'index.php');
                        $label = (string) ($item['label'] ?? $file);
                        $icon = (string) ($item['icon'] ?? 'fallback');
                        $isActive = $activeFile === $file;
                        ?>
                        <a
                            href="<?= e(base_url($file)) ?>"
                            class="tube-link<?= $isActive ? ' active' : '' ?>"
                            data-tube-link
                            aria-label="<?= e($label) ?>"
                        >
                            <span class="tube-icon" aria-hidden="true"><?= tubelight_icon_svg($icon) ?></span>
                            <span class="tube-label"><?= e($label) ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <?php if ($showLang): ?>
                    <div class="tube-lang" aria-label="Language switcher">
                        <a href="<?= e(lang_url('fr')) ?>" class="<?= current_lang() === 'fr' ? 'active' : '' ?>">FR</a>
                        <a href="<?= e(lang_url('de')) ?>" class="<?= current_lang() === 'de' ? 'active' : '' ?>">DE</a>
                    </div>
                <?php endif; ?>
            </div>
        </header>
        <?php
    }
}
