<?php
/**
 * Logo Daris : marque SVG en ligne + nom.
 *
 * @var \Daris\View\View $v
 * @var array<string, string> $app
 * @var string $variant  "header" | "footer" | "compact"
 */
require __DIR__ . '/app-fallback.php';

$variant = $variant ?? 'header';
$uid = 'logo-' . $variant;
?>
<a class="logo logo--<?= $v->e($variant) ?>" href="<?= $v->url('/') ?>" aria-label="<?= $v->e($app['name']) ?>, accueil">
    <svg class="logo__mark" viewBox="0 0 64 64" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="<?= $uid ?>-sky" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#eaf4fd"/><stop offset="1" stop-color="#bcdcf5"/></linearGradient>
            <linearGradient id="<?= $uid ?>-petal" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#fbe3ec"/><stop offset="1" stop-color="#f2a9c3"/></linearGradient>
        </defs>
        <circle cx="32" cy="32" r="30" fill="url(#<?= $uid ?>-sky)"/>
        <circle cx="32" cy="32" r="30" fill="none" stroke="#1f3a68" stroke-width="1.4"/>
        <circle cx="32" cy="32" r="25.5" fill="none" stroke="#ffffff" stroke-opacity=".8" stroke-width="1"/>
        <path d="M25 15.5 C 47 15.5, 52.5 29, 52.5 32 C 52.5 35, 47 48.5, 25 48.5 C 36.5 40, 36.5 24, 25 15.5 Z" fill="url(#<?= $uid ?>-petal)"/>
        <rect x="21" y="14" width="3.2" height="36" rx="1.6" fill="#1f3a68"/>
        <path d="M33.5 32 L41.5 23.5" stroke="#1f3a68" stroke-width="1.8" stroke-linecap="round"/>
        <circle cx="33.5" cy="32" r="2" fill="#1f3a68"/>
        <circle cx="33.5" cy="32" r=".8" fill="#f2a9c3"/>
        <path d="M46 11.5 l1.3 3.2 3.2 1.3 -3.2 1.3 -1.3 3.2 -1.3 -3.2 -3.2 -1.3 3.2 -1.3 z" fill="#1f3a68"/>
    </svg>
    <span class="logo__text">
        <span class="logo__name"><?= $v->e($app['name']) ?></span>
        <span class="logo__tagline"><?= $v->e($app['tagline']) ?></span>
    </span>
</a>
