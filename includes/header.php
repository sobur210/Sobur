<?php
/**
 * Site header + document head.
 * Expects optional: $pageTitle, $pageDescription.
 */
require_once __DIR__ . '/helpers.php';
app_boot();
secure_session_start();

$pageTitle = $pageTitle ?? (config('site.name') . ' — Graphic designer & software engineer');
$pageDescription = $pageDescription ?? 'Brand identity, visuals and full web products from one person, from the first sketch to the live site.';
$canonical = rtrim(config('site.url') ?: '', '/') . '/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">
    <link rel="canonical" href="<?= e($canonical) ?>">

    <!-- Open Graph / Twitter -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?= e($pageTitle) ?>">
    <meta property="og:description" content="<?= e($pageDescription) ?>">
    <meta property="og:url" content="<?= e($canonical) ?>">
    <meta property="og:image" content="<?= e(base_url('assets/img/og-image.png')) ?>">
    <meta property="og:site_name" content="<?= e(config('site.name')) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($pageTitle) ?>">
    <meta name="twitter:description" content="<?= e($pageDescription) ?>">
    <meta name="twitter:image" content="<?= e(base_url('assets/img/og-image.png')) ?>">

    <meta name="theme-color" content="#0A7F52">
    <link rel="icon" href="<?= e(base_url('favicon.svg')) ?>" type="image/svg+xml">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Sora:wght@500;600;700;800&display=swap">

    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>

    <header class="site-header">
        <div class="container nav">
            <a class="wordmark" href="#top" aria-label="<?= e(config('site.name')) ?>, back to top"><?= e(config('site.name')) ?></a>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav" aria-label="Toggle menu">
                <span></span>
            </button>

            <ul class="nav-links" id="primary-nav">
                <li><a href="#work">Work</a></li>
                <li><a href="#design">Design</a></li>
                <li><a href="#about">About</a></li>
                <li><a class="btn btn--primary" href="#contact">Hire me</a></li>
            </ul>
        </div>
    </header>
