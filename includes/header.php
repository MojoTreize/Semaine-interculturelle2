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
    ['file' => 'index.php', 'label' => t('nav.home'), 'icon' => 'home'],
    ['file' => 'about.php', 'label' => t('nav.about'), 'icon' => 'about'],
    ['file' => 'program.php', 'label' => t('nav.program'), 'icon' => 'program'],
    ['file' => 'registration.php', 'label' => t('nav.registration'), 'icon' => 'registration'],
    ['file' => 'contribute.php', 'label' => t('nav.contribute'), 'icon' => 'contribute'],
    ['file' => 'partners.php', 'label' => t('nav.partners'), 'icon' => 'partners'],
    ['file' => 'contact.php', 'label' => t('nav.contact'), 'icon' => 'contact'],
];

$currentFile = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
$flash = get_flash();

require_once ROOT_PATH . '/includes/components/tubelight_nav.php';
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
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
</head>
<body>
<?php render_tubelight_navbar($navItems, $currentFile); ?>
<main class="site-main">
    <?php if ($flash): ?>
        <div class="container">
            <div class="alert alert-<?= e($flash['type']) ?>">
                <?= e($flash['message']) ?>
            </div>
        </div>
    <?php endif; ?>
