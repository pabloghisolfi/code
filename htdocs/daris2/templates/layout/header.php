<?php
/**
 * En-tête Azurine : bande promo, barre principale, menu latéral.
 *
 * @var \Daris\View\View $v
 * @var array<string, string|int> $app
 * @var string $currentPath
 * @var list<\Daris\Catalog\Category> $navCategories
 * @var int $cartCount
 */
require __DIR__ . '/../partials/app-fallback.php';

$isActive = static fn (string $prefix): bool => $prefix === '/' ? $currentPath === '/' : str_starts_with($currentPath, $prefix);
$navItems = [
    ['/boutique', 'Boutique'],
    ['/exemples', 'Exemples'],
    ['/conseils', 'Conseils'],
    ['/chat', 'Chat'],
    ['/tarifs', 'Tarifs'],
    ['/a-propos', 'À propos'],
    ['/contact', 'Contact'],
];
?>
<div class="promo-bar" data-ribbon>
    <div class="wrap promo-bar__inner">
        <p class="promo-bar__item is-visible">Livraison offerte dès CHF 80 · Retours gratuits 30 jours</p>
        <p class="promo-bar__item">Code <strong>BIENVENUE10</strong> : −10 % sur votre première commande</p>
        <p class="promo-bar__item">Expédition depuis Lausanne, le jour même avant 14 h</p>
    </div>
</div>

<header class="topbar" data-header>
    <div class="wrap topbar__inner">
        <button class="topbar__burger" type="button" aria-expanded="false" aria-controls="drawer" data-drawer-open>
            <svg aria-hidden="true"><use href="#i-menu"/></svg><span class="sr-only">Menu</span>
        </button>

        <?= $v->partial('partials/logo', ['variant' => 'header']) ?>

        <nav class="topnav" aria-label="Navigation principale">
            <ul class="topnav__list">
                <?php foreach ($navItems as [$path, $label]) : ?>
                <li><a class="<?= $v->classes(['topnav__link' => true, 'is-active' => $isActive($path)]) ?>" href="<?= $v->url($path) ?>"<?= $isActive($path) ? ' aria-current="page"' : '' ?>><?= $v->e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="topbar__tools">
            <form class="search" role="search" action="<?= $v->url('/boutique') ?>" method="get" data-search>
                <label class="sr-only" for="q">Rechercher</label>
                <input class="search__input" id="q" type="search" name="q" placeholder="Rechercher…" autocomplete="off" maxlength="80">
                <button class="search__btn" type="submit" aria-label="Rechercher"><svg aria-hidden="true"><use href="#i-search"/></svg></button>
            </form>
            <a class="topbar__cart" href="<?= $v->url('/panier') ?>" data-cart-link>
                <svg aria-hidden="true"><use href="#i-bag"/></svg>
                <span class="topbar__cart-label">Panier</span>
                <span class="topbar__cart-count" data-cart-count<?= $cartCount === 0 ? ' hidden' : '' ?>><?= $cartCount ?></span>
            </a>
        </div>
    </div>
</header>

<div class="drawer" id="drawer" hidden data-drawer>
    <div class="drawer__backdrop" data-drawer-close></div>
    <div class="drawer__panel" role="dialog" aria-modal="true" aria-label="Menu">
        <div class="drawer__head">
            <?= $v->partial('partials/logo', ['variant' => 'compact']) ?>
            <button class="drawer__close" type="button" data-drawer-close aria-label="Fermer"><svg aria-hidden="true"><use href="#i-close"/></svg></button>
        </div>
        <form class="search search--drawer" role="search" action="<?= $v->url('/boutique') ?>" method="get">
            <label class="sr-only" for="q-drawer">Rechercher</label>
            <input class="search__input" id="q-drawer" type="search" name="q" placeholder="Rechercher…" maxlength="80">
            <button class="search__btn" type="submit" aria-label="Rechercher"><svg aria-hidden="true"><use href="#i-search"/></svg></button>
        </form>
        <nav aria-label="Menu mobile">
            <ul class="drawer__list drawer__list--main">
                <?php foreach ($navItems as [$path, $label]) : ?>
                <li><a href="<?= $v->url($path) ?>"><?= $v->e($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php foreach ($navCategories as $category) : ?>
            <p class="drawer__title"><a href="<?= $v->url('/boutique/' . $category->slug()) ?>"><?= $v->e($category->name()) ?></a></p>
            <ul class="drawer__list">
                <?php foreach ($category->subcategories() as $sub) : ?>
                <li><a href="<?= $v->url('/boutique/' . $category->slug(), ['sous' => $sub['slug']]) ?>"><?= $v->e($sub['name']) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <?php endforeach; ?>
            <ul class="drawer__list">
                <li><a href="<?= $v->url('/commande/suivi') ?>">Suivre ma commande</a></li>
                <li><a href="<?= $v->url('/confidentialite') ?>">Confidentialité</a></li>
                <li><a href="<?= $v->url('/cgv') ?>">Conditions générales</a></li>
            </ul>
        </nav>
    </div>
</div>
