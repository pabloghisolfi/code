<?php
/**
 * Accueil Azurine — multimarque luxe.
 *
 * @var \Daris\View\View $v
 * @var list<\Daris\Catalog\Category> $categories
 * @var list<\Daris\Catalog\Product> $featured
 * @var list<\Daris\Catalog\Product> $newProducts
 * @var list<\Daris\Catalog\Look> $looks
 * @var list<array{q: string, a: string}> $faq
 * @var array<string, array{min: int, max: int}> $priceBounds
 * @var list<string> $watchBrands
 * @var list<string> $makeupBrands
 * @var int $productCount
 */
use Daris\View\ProductArt;

$heroWatch = null;
$heroMakeup = null;
foreach ($featured as $p) {
    if ($heroWatch === null && $p->isWatch()) {
        $heroWatch = $p;
    }
    if ($heroMakeup === null && !$p->isWatch()) {
        $heroMakeup = $p;
    }
}
if ($heroWatch === null || $heroMakeup === null) {
    foreach ($featured as $p) {
        if ($heroWatch === null && $p->isWatch()) {
            $heroWatch = $p;
        }
        if ($heroMakeup === null && !$p->isWatch()) {
            $heroMakeup = $p;
        }
    }
}
?>
<section class="maison-hero">
    <div class="wrap maison-hero__grid">
        <div class="maison-hero__copy" data-reveal>
            <p class="eyebrow">Azurine · Lausanne · CHF</p>
            <h1 class="maison-hero__title">Haute beauté<br>&amp; horlogerie</h1>
            <p class="lead">Une sélection multimarque des plus grandes maisons — maquillage iconique et montres d'exception — proposée par Azurine à des prix suisses clairs. Visuels originaux, marques citées en toutes lettres : boutique indépendante, pas boutique officielle des manufactures.</p>
            <div class="actions">
                <a class="btn btn--primary btn--lg" href="<?= $v->url('/boutique/montres') ?>">Montres</a>
                <a class="btn btn--rose btn--lg" href="<?= $v->url('/boutique/maquillage') ?>">Maquillage</a>
            </div>
            <ul class="maison-hero__stats">
                <li><strong><?= $productCount ?></strong> références</li>
                <li><strong><?= count($watchBrands) + count($makeupBrands) ?>+</strong> maisons</li>
                <li>Conseillère sur chaque page</li>
            </ul>
            <nav class="maison-hero__nav" aria-label="Accès rapide">
                <a href="<?= $v->url('/tarifs') ?>">Tarifs</a>
                <a href="<?= $v->url('/exemples') ?>">Exemples</a>
                <a href="<?= $v->url('/conseils') ?>">Conseils</a>
                <a href="<?= $v->url('/chat') ?>">Chat</a>
                <a href="<?= $v->url('/a-propos') ?>">À propos</a>
                <a href="<?= $v->url('/contact') ?>">Contact</a>
            </nav>
        </div>
        <div class="maison-hero__showcase" data-reveal>
            <?php if ($heroWatch !== null) : ?>
            <a class="maison-tile maison-tile--watch" href="<?= $v->url('/produit/' . $heroWatch->slug()) ?>">
                <?= $v->partial('partials/product-media', ['product' => $heroWatch, 'class' => 'maison-tile__art']) ?>
                <span class="maison-tile__meta">
                    <span class="maison-tile__brand"><?= $v->e($heroWatch->brand()) ?></span>
                    <span class="maison-tile__name"><?= $v->e($heroWatch->name()) ?></span>
                    <span class="maison-tile__price"><?= $v->money($heroWatch->price()) ?></span>
                </span>
            </a>
            <?php endif; ?>
            <?php if ($heroMakeup !== null) : ?>
            <a class="maison-tile maison-tile--makeup" href="<?= $v->url('/produit/' . $heroMakeup->slug()) ?>">
                <?= $v->partial('partials/product-media', ['product' => $heroMakeup, 'class' => 'maison-tile__art']) ?>
                <span class="maison-tile__meta">
                    <span class="maison-tile__brand"><?= $v->e($heroMakeup->brand()) ?></span>
                    <span class="maison-tile__name"><?= $v->e($heroMakeup->name()) ?></span>
                    <span class="maison-tile__price"><?= $v->money($heroMakeup->price()) ?></span>
                </span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section section--flush">
    <div class="wrap">
        <div class="brand-rail brand-rail--blue" data-reveal>
            <p class="brand-rail__label">Horlogerie</p>
            <ul class="brand-rail__list">
                <?php foreach ($watchBrands as $brand) : ?>
                <li><a href="<?= $v->url('/boutique/montres', ['marque' => $brand]) ?>"><?= $v->e($brand) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="brand-rail brand-rail--pink" data-reveal>
            <p class="brand-rail__label">Beauté</p>
            <ul class="brand-rail__list">
                <?php foreach ($makeupBrands as $brand) : ?>
                <li><a href="<?= $v->url('/boutique/maquillage', ['marque' => $brand]) ?>"><?= $v->e($brand) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>

<section class="section section--deep">
    <div class="wrap">
        <div class="section-head section-head--center" data-reveal>
            <p class="eyebrow">Les univers</p>
            <h2 class="h2">Deux galeries, une même exigence</h2>
        </div>
        <div class="home-cats home-cats--lux">
            <?php foreach ($categories as $category) :
                $bounds = $priceBounds[$category->slug()] ?? ['min' => 0, 'max' => 0];
                $isWatch = $category->slug() === 'montres';
                ?>
            <a class="<?= $v->classes(['home-cat' => true, 'home-cat--lux' => true, 'home-cat--pink' => !$isWatch, 'home-cat--blue' => $isWatch]) ?>" href="<?= $v->url('/boutique/' . $category->slug()) ?>" data-reveal>
                <div class="home-cat__art"><?= ProductArt::render($isWatch ? 'watch-round' : 'lipstick', $isWatch ? '#5a9fd4' : '#e08aad', $isWatch ? 'blue' : 'pink', 'home-cat__svg', '', $isWatch ? 'Rolex' : 'Chanel') ?></div>
                <div>
                    <p class="eyebrow"><?= $v->e($category->name()) ?></p>
                    <h3 class="h3"><?= $v->e($category->title()) ?></h3>
                    <p><?= $v->e($category->intro()) ?></p>
                    <span class="home-cat__price">De <?= $v->money($bounds['min']) ?> à <?= $v->money($bounds['max']) ?></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Sélection', 'title' => 'Pièces remarquables', 'text' => 'Montres et maquillage des maisons les plus demandées, disponibles chez Azurine.', 'linkHref' => $v->url('/boutique'), 'linkLabel' => 'Voir tout le catalogue']) ?>
        <div class="grid grid--products grid--lux">
            <?php foreach ($featured as $product) : ?>
            <?= $v->partial('partials/product-card', ['product' => $product]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--pink">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Exemples', 'title' => 'Tenues & cadrans assortis', 'text' => 'Idées de looks avec références maquillage et montre.', 'linkHref' => $v->url('/exemples'), 'linkLabel' => 'Tous les exemples']) ?>
        <div class="grid grid--looks">
            <?php foreach ($looks as $look) : ?>
            <?= $v->partial('partials/look-card', ['look' => $look]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--deep">
    <div class="wrap split">
        <div data-reveal>
            <p class="eyebrow">Conseillère rose</p>
            <h2 class="h2">Léa vous répond sur chaque page</h2>
            <p class="lead">Teintes Chanel ou Dior, taille de boîtier Rolex ou Cartier, livraison assurée : ouvrez le panneau rose clair en bas à droite.</p>
            <div class="actions">
                <a class="btn btn--primary" href="<?= $v->url('/conseils') ?>">Guide complet</a>
                <a class="btn btn--ghost" href="<?= $v->url('/chat') ?>">Page chat</a>
            </div>
        </div>
        <div class="faq" data-reveal>
            <?php foreach ($faq as $item) : ?>
            <details class="faq__item"><summary><?= $v->e($item['q']) ?></summary><p><?= $v->e($item['a']) ?></p></details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="cta-band cta-band--lux" data-reveal>
            <div>
                <p class="eyebrow eyebrow--light">Azurine</p>
                <h2 class="h2">Livraison soignée en Suisse</h2>
                <p>Montres assurées · Maquillage protégé · Paiement TWINT ou carte</p>
            </div>
            <a class="btn btn--light btn--lg" href="<?= $v->url('/boutique') ?>">Entrer en boutique <svg aria-hidden="true"><use href="#i-arrow"/></svg></a>
        </div>
    </div>
</section>
