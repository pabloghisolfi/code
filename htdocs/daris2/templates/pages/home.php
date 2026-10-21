<?php
/**
 * Accueil Azurine.
 *
 * @var \Daris\View\View $v
 * @var list<\Daris\Catalog\Category> $categories
 * @var list<\Daris\Catalog\Product> $featured
 * @var list<\Daris\Catalog\Product> $newProducts
 * @var list<\Daris\Catalog\Look> $looks
 * @var list<array{q: string, a: string}> $faq
 * @var array<string, array{min: int, max: int}> $priceBounds
 */
use Daris\View\ProductArt;

$heroProducts = array_slice($featured, 0, 4);
?>
<section class="home-hero">
    <div class="wrap home-hero__grid">
        <div class="home-hero__intro" data-reveal>
            <p class="eyebrow">Boutique suisse · CHF</p>
            <h1 class="home-hero__title">Du rose sur les joues,<br>du bleu au poignet.</h1>
            <p class="lead">Azurine réunit maquillage doux et montres élégantes pour les jeunes femmes qui aiment les jolies choses bien faites. Prix tout compris, livraison offerte dès CHF 80.</p>
            <div class="actions">
                <a class="btn btn--primary btn--lg" href="<?= $v->url('/boutique/maquillage') ?>">Maquillage <svg aria-hidden="true"><use href="#i-arrow"/></svg></a>
                <a class="btn btn--ghost btn--lg" href="<?= $v->url('/boutique/montres') ?>">Montres</a>
            </div>
            <ul class="home-hero__tags">
                <li>28 produits</li>
                <li>Dès CHF 14</li>
                <li>Garantie 2 ans</li>
            </ul>
        </div>
        <div class="home-bento" data-reveal>
            <?php foreach ($heroProducts as $i => $product) : ?>
            <a class="home-bento__cell home-bento__cell--<?= $i + 1 ?>" href="<?= $v->url('/produit/' . $product->slug()) ?>">
                <?= ProductArt::product($product, 'home-bento__art') ?>
                <span class="home-bento__label"><?= $v->e($product->name()) ?> <strong><?= $v->money($product->price()) ?></strong></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--deep">
    <div class="wrap">
        <div class="section-head section-head--center" data-reveal>
            <p class="eyebrow">Deux univers</p>
            <h2 class="h2">Choisissez votre porte d'entrée</h2>
            <p class="lead">Maquillage pastel ou montres fines : chaque rayon a ses conseils, ses teintes et ses prix en francs suisses.</p>
        </div>
        <div class="home-cats">
            <?php foreach ($categories as $category) :
                $bounds = $priceBounds[$category->slug()] ?? ['min' => 0, 'max' => 0];
                $isWatch = $category->slug() === 'montres';
                ?>
            <a class="<?= $v->classes(['home-cat' => true, 'home-cat--pink' => !$isWatch, 'home-cat--blue' => $isWatch]) ?>" href="<?= $v->url('/boutique/' . $category->slug()) ?>" data-reveal>
                <div class="home-cat__art"><?= ProductArt::render($isWatch ? 'watch-round' : 'lipstick', $isWatch ? '#7eb8e8' : '#e8a0b8', $isWatch ? 'pink' : 'blue', 'home-cat__svg') ?></div>
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
        <?= $v->partial('partials/section-head', ['kicker' => 'Les préférés', 'title' => 'Ce que vous commandez le plus', 'text' => 'Best-sellers maquillage et montres, avec les avis qui vont avec.', 'linkHref' => $v->url('/boutique'), 'linkLabel' => 'Toute la boutique']) ?>
        <div class="grid grid--products">
            <?php foreach ($featured as $product) : ?>
            <?= $v->partial('partials/product-card', ['product' => $product]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--pink">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Exemples', 'title' => 'Un look complet, étape par étape', 'text' => 'Matin frais, soirée ou cérémonie : produits, gestes et montre assortie.', 'linkHref' => $v->url('/exemples'), 'linkLabel' => 'Tous les exemples']) ?>
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
            <p class="eyebrow">Conseils</p>
            <h2 class="h2">Teintes, tailles, entretien</h2>
            <p class="lead">Le guide et le chat vous aident à choisir sans vous tromper. L'échange de teinte est gratuit.</p>
            <div class="actions">
                <a class="btn btn--primary" href="<?= $v->url('/conseils') ?>">Ouvrir les conseils</a>
                <a class="btn btn--ghost" href="<?= $v->url('/chat') ?>">Discuter avec Léa</a>
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
        <div class="cta-band" data-reveal>
            <div>
                <p class="eyebrow eyebrow--light">Première commande</p>
                <h2 class="h2">−10 % avec <span class="code">BIENVENUE10</span></h2>
                <p>Livraison offerte dès CHF 80 · Retours gratuits 30 jours</p>
            </div>
            <a class="btn btn--light btn--lg" href="<?= $v->url('/boutique') ?>">Commencer <svg aria-hidden="true"><use href="#i-arrow"/></svg></a>
        </div>
    </div>
</section>
