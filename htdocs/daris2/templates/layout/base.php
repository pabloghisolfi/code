<?php
/**
 * Gabarit de base : <head>, en-tête, contenu de page, pied de page.
 *
 * @var \Daris\View\View $v
 * @var string $content
 * @var array<string, mixed> $meta
 * @var array<string, string|int> $app
 * @var string $currentPath
 * @var list<\Daris\Catalog\Category> $navCategories
 * @var int $cartCount
 * @var array{type: string, message: string}|null $flash
 */
require __DIR__ . '/../partials/app-fallback.php';

$title = (string) ($meta['title'] ?? $app['name']);
$description = (string) ($meta['description'] ?? '');
$canonical = (string) ($meta['canonical'] ?? '');
$ogType = (string) ($meta['ogType'] ?? 'website');
$bodyClass = (string) ($meta['bodyClass'] ?? '');
$noindex = (bool) ($meta['noindex'] ?? false);
?>
<!DOCTYPE html>
<html lang="fr-CH">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= $v->e($title) ?></title>
<meta name="description" content="<?= $v->e($description) ?>">
<meta name="theme-color" content="<?= $v->e((string) $app['themeColor']) ?>">
<?php if ($noindex) : ?>
<meta name="robots" content="noindex, follow">
<?php endif; ?>
<?php if ($canonical !== '') : ?>
<link rel="canonical" href="<?= $v->e($canonical) ?>">
<?php endif; ?>
<meta property="og:site_name" content="<?= $v->e((string) $app['name']) ?>">
<meta property="og:type" content="<?= $v->e($ogType) ?>">
<meta property="og:locale" content="fr_CH">
<meta property="og:title" content="<?= $v->e($title) ?>">
<meta property="og:description" content="<?= $v->e($description) ?>">
<?php if ($canonical !== '') : ?>
<meta property="og:url" content="<?= $v->e($canonical) ?>">
<?php endif; ?>
<meta property="og:image" content="<?= $v->absoluteUrl('/assets/img/og-image.png') ?>">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?= $v->asset('img/favicon.svg') ?>" type="image/svg+xml">
<link rel="apple-touch-icon" href="<?= $v->asset('img/apple-touch-icon.png') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,500&family=Jost:wght@300;400;500;600&display=swap">
<link rel="stylesheet" href="<?= $v->asset('css/style.css') ?>">
<script src="<?= $v->asset('js/app.js') ?>" defer></script>
<?php if (isset($meta['jsonLd']) && is_array($meta['jsonLd'])) : ?>
<script type="application/ld+json"><?= json_encode($meta['jsonLd'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php endif; ?>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'OnlineStore',
    'name' => $app['name'],
    'description' => 'Boutique en ligne suisse de maquillage et de montres.',
    'url' => $v->absoluteUrl('/'),
    'logo' => $v->absoluteUrl('/assets/img/logo-mark.svg'),
    'email' => $app['email'],
    'telephone' => $app['phone'],
    'address' => ['@type' => 'PostalAddress', 'streetAddress' => 'Rue du Lac 12', 'postalCode' => '1003', 'addressLocality' => 'Lausanne', 'addressCountry' => 'CH'],
    'currenciesAccepted' => 'CHF',
    'paymentAccepted' => 'Facture, TWINT, Carte de crédit',
    'areaServed' => 'CH',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
</head>
<body class="<?= $v->e(trim($bodyClass . ' ' . ($cartCount > 0 ? 'has-cart' : ''))) ?>" data-base="<?= $v->url('/') ?>" data-csrf="<?= $v->csrfToken() ?>">
<a class="skip-link" href="#contenu">Aller au contenu</a>
<?= $v->partial('partials/icons') ?>
<?= $v->partial('layout/header') ?>
<main id="contenu" tabindex="-1">
<?php if ($flash !== null) : ?>
<div class="wrap"><div class="flash flash--<?= $v->e($flash['type']) ?>" role="status" data-flash><?= $v->e($flash['message']) ?><button type="button" class="flash__close" aria-label="Fermer" data-flash-close><svg aria-hidden="true"><use href="#i-close"/></svg></button></div></div>
<?php endif; ?>
<?= $content ?>
</main>
<?= $v->partial('layout/footer') ?>
<div class="toast" id="toast" role="status" aria-live="polite" hidden></div>
</body>
</html>
