<?php
/**
 * Logo Azurine (script + vague).
 *
 * @var \Daris\View\View $v
 * @var array<string, string> $app
 * @var string $variant  "header" | "footer" | "compact"
 */
require __DIR__ . '/app-fallback.php';

$variant = $variant ?? 'header';
$uid = 'az-' . $variant;
?>
<a class="brand brand--<?= $v->e($variant) ?>" href="<?= $v->url('/') ?>" aria-label="<?= $v->e($app['name']) ?>, accueil">
    <svg class="brand__wave" viewBox="0 0 56 56" aria-hidden="true" focusable="false">
        <defs>
            <linearGradient id="<?= $uid ?>-b" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#7eb8e8"/><stop offset="1" stop-color="#3d7eb8"/></linearGradient>
            <linearGradient id="<?= $uid ?>-p" x1="0" y1="1" x2="1" y2="0"><stop offset="0" stop-color="#f0b8d0"/><stop offset="1" stop-color="#c45685"/></linearGradient>
        </defs>
        <circle cx="28" cy="28" r="26" fill="url(#<?= $uid ?>-b)" opacity=".25"/>
        <path d="M8 36 Q 20 18, 34 28 T 50 22" fill="none" stroke="url(#<?= $uid ?>-p)" stroke-width="3" stroke-linecap="round"/>
        <circle cx="28" cy="28" r="24" fill="none" stroke="#1e4d78" stroke-width="1.5"/>
    </svg>
    <?php if ($variant !== 'compact') : ?>
    <span class="brand__text">
        <span class="brand__name"><?= $v->e($app['name']) ?></span>
        <span class="brand__tagline"><?= $v->e($app['tagline']) ?></span>
    </span>
    <?php endif; ?>
</a>
