<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? t('seo.default_title');
$pageDescription = $pageDescription ?? t('seo.default_description');
$pageKeywords = $pageKeywords ?? t('seo.default_keywords');

$rawRequestUri = $_SERVER['REQUEST_URI'] ?? '/';
$pathPart = strtok($rawRequestUri, '?') ?: '/';
$scheme = is_https_request() ? 'https' : 'http';
$host = (string) ($_SERVER['HTTP_HOST'] ?? parse_url((string) app_config('app.base_url', ''), PHP_URL_HOST));
$canonicalUrl = $canonicalUrl ?? ($host !== '' ? $scheme . '://' . $host . $pathPart : base_url(ltrim($pathPart, '/')));

$navItems = [
    'index.php' => t('nav.home'),
    'about.php' => t('nav.about'),
    'program.php' => t('nav.program'),
    'registration.php' => t('nav.registration'),
    'contribute.php' => t('nav.contribute'),
    'partners.php' => t('nav.partners'),
    'contact.php' => t('nav.contact'),
];

$currentFile = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="keywords" content="<?= e($pageKeywords) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e((string) app_config('app.name', 'Guinee Dortmund 2026')) ?>">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($canonicalUrl) ?>">
    <meta property="og:image" content="<?= e(base_url('assets/images/logo.svg')) ?>">
    <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <link rel="preload" href="<?= e(base_url('assets/css/style.css')) ?>" as="style">
    <link rel="stylesheet" href="<?= e(base_url('assets/css/style.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="container topbar">
        <a class="brand" href="<?= e(base_url('index.php')) ?>">
            <img src="<?= e(base_url('assets/images/logo.svg')) ?>" alt="Logo Guinee Dortmund 2026" width="56" height="56">
            <span><?= e(t('site.short_name')) ?></span>
        </a>
        <button class="menu-toggle" type="button" aria-label="Menu" data-menu-toggle>
            <span></span><span></span><span></span>
        </button>
        <nav class="main-nav" data-main-nav>
            <?php foreach ($navItems as $file => $label): ?>
                <a href="<?= e(base_url($file)) ?>" class="<?= $currentFile === $file ? 'active' : '' ?>"><?= e($label) ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="lang-switch" aria-label="Language switcher">
            <a href="<?= e(lang_url('fr')) ?>" class="<?= current_lang() === 'fr' ? 'active' : '' ?>">FR</a>
            <a href="<?= e(lang_url('de')) ?>" class="<?= current_lang() === 'de' ? 'active' : '' ?>">DE</a>
        </div>
    </div>
</header>
<main>
    <?php if ($flash): ?>
        <div class="container">
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        </div>
    <?php endif; ?>
