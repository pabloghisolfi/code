<?php
/**
 * En-tête Azurine : bande promo, barre principale, menu latéral.
 *
 * @var \Daris\View\View $v
 * @var array<string, string|int> $app
 * @var string $currentPath
 * @var list<\Daris\Catalog\Category> $navCategories
 * @var int $cartCount
 * @var string $locale
 * @var array<string, string> $localeLabels
 */
require __DIR__ . '/../partials/app-fallback.php';

$isActive = static fn (string $prefix): bool => $prefix === '/' ? $currentPath === '/' : str_starts_with($currentPath, $prefix);
$navItems = [
    ['/boutique', 'nav.boutique', 'Boutique'],
    ['/exemples', 'nav.examples', 'Exemples'],
    ['/conseils', 'nav.tips', 'Conseils'],
    ['/chat', 'nav.chat', 'Chat'],
    ['/tarifs', 'nav.pricing', 'Tarifs'],
    ['/a-propos', 'nav.about', 'À propos'],
    ['/contact', 'nav.contact', 'Contact'],
];
?>
<div class="promo-bar" data-ribbon>
    <div class="wrap promo-bar__inner">
        <p class="promo-bar__item is-visible"><?= $v->e($v->t('promo.line1', 'Multimarque · Prix CHF')) ?></p>
    </div>
</div>

<header class="topbar" data-header>
    <div class="wrap topbar__inner">
        <button class="topbar__burger" type="button" aria-expanded="false" aria-controls="drawer" data-drawer-open>
            <svg aria-hidden="true"><use href="#i-menu"/></svg><span class="sr-only"><?= $v->e($v->t('nav.menu', 'Menu')) ?></span>
        </button>

        <?= $v->partial('partials/logo', ['variant' => 'header']) ?>

        <nav class="topnav" aria-label="<?= $v->e($v->t('nav.main', 'Navigation principale')) ?>">
            <ul class="topnav__list">
                <?php foreach ($navItems as [$path, $key, $fallback]) : ?>
                <li><a class="<?= $v->classes(['topnav__link' => true, 'is-active' => $isActive($path)]) ?>" href="<?= $v->url($path) ?>"<?= $isActive($path) ? ' aria-current="page"' : '' ?>><?= $v->e($v->t($key, $fallback)) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="topbar__tools">
            <form class="locale-switcher" method="post" action="<?= $v->url('/locale') ?>" data-locale-form>
                <?= $v->csrfField() ?>
                <input type="hidden" name="back" value="<?= $v->e($currentPath) ?>">
                <label class="sr-only" for="locale-select"><?= $v->e($v->t('lang.label', 'Langue')) ?></label>
                <select id="locale-select" name="locale" aria-label="<?= $v->e($v->t('lang.label', 'Langue')) ?>" onchange="this.form.requestSubmit()">
                    <?php foreach ($localeLabels as $code => $label) : ?>
                    <option value="<?= $v->e($code) ?>" <?= ($locale ?? 'fr') === $code ? 'selected' : '' ?>><?= $v->e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <form class="search" role="search" action="<?= $v->url('/boutique') ?>" method="get" data-search>
                <label class="sr-only" for="q"><?= $v->e($v->t('nav.search', 'Rechercher')) ?></label>
                <input class="search__input" id="q" type="search" name="q" placeholder="<?= $v->e($v->t('nav.search_placeholder', 'Rechercher…')) ?>" autocomplete="off" maxlength="80">
                <button class="search__btn" type="submit" aria-label="<?= $v->e($v->t('nav.search', 'Rechercher')) ?>"><svg aria-hidden="true"><use href="#i-search"/></svg></button>
            </form>
            <a class="topbar__cart" href="<?= $v->url('/panier') ?>" data-cart-link>
                <svg aria-hidden="true"><use href="#i-bag"/></svg>
                <span class="topbar__cart-label"><?= $v->e($v->t('nav.cart', 'Panier')) ?></span>
                <span class="topbar__cart-count" data-cart-count<?= $cartCount === 0 ? ' hidden' : '' ?>><?= $cartCount ?></span>
            </a>
        </div>
    </div>
</header>

<div class="drawer" id="drawer" hidden data-drawer>
    <div class="drawer__backdrop" data-drawer-close></div>
    <div class="drawer__panel" role="dialog" aria-modal="true" aria-label="<?= $v->e($v->t('nav.menu', 'Menu')) ?>">
        <div class="drawer__head">
            <?= $v->partial('partials/logo', ['variant' => 'compact']) ?>
            <button class="drawer__close" type="button" data-drawer-close aria-label="<?= $v->e($v->t('photo.close', 'Fermer')) ?>"><svg aria-hidden="true"><use href="#i-close"/></svg></button>
        </div>
        <form class="search search--drawer" role="search" action="<?= $v->url('/boutique') ?>" method="get">
            <label class="sr-only" for="q-drawer"><?= $v->e($v->t('nav.search', 'Rechercher')) ?></label>
            <input class="search__input" id="q-drawer" type="search" name="q" placeholder="<?= $v->e($v->t('nav.search_placeholder', 'Rechercher…')) ?>" maxlength="80">
            <button class="search__btn" type="submit" aria-label="<?= $v->e($v->t('nav.search', 'Rechercher')) ?>"><svg aria-hidden="true"><use href="#i-search"/></svg></button>
        </form>
        <nav aria-label="<?= $v->e($v->t('nav.main', 'Navigation')) ?>">
            <ul class="drawer__list drawer__list--main">
                <?php foreach ($navItems as [$path, $key, $fallback]) : ?>
                <li><a href="<?= $v->url($path) ?>"><?= $v->e($v->t($key, $fallback)) ?></a></li>
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
        </nav>
    </div>
</div>
