<?php
/**
 * Accueil.
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

$heroProducts = array_slice($featured, 0, 3);
?>
<section class="hero">
    <div class="wrap hero__inner">
        <div class="hero__copy" data-reveal>
            <p class="kicker"><svg class="kicker__icon" aria-hidden="true"><use href="#i-sparkle"/></svg> Nouvelle collection automne</p>
            <h1 class="hero__title">Du <em>rose</em> aux joues,<br>du <em>bleu</em> au poignet.</h1>
            <p class="lead">Maquillage doux et montres élégantes, choisis pour les filles et les jeunes femmes qui aiment les jolies choses bien faites. Prix en francs suisses, tout compris, livraison offerte dès CHF 80.</p>
            <div class="hero__actions">
                <a class="btn btn--primary btn--lg" href="<?= $v->url('/boutique/maquillage') ?>">Découvrir le maquillage <svg aria-hidden="true"><use href="#i-arrow"/></svg></a>
                <a class="btn btn--soft btn--lg" href="<?= $v->url('/boutique/montres') ?>">Voir les montres</a>
            </div>
            <ul class="hero__facts">
                <li><strong>28</strong><span>produits choisis avec soin</span></li>
                <li><strong>CHF 14</strong><span>premier prix, vernis Pastel</span></li>
                <li><strong>2 ans</strong><span>de garantie sur chaque montre</span></li>
            </ul>
        </div>
        <div class="hero__gallery" aria-hidden="true">
            <?php foreach ($heroProducts as $i => $product) : ?>
            <a class="hero__tile hero__tile--<?= $i + 1 ?>" href="<?= $v->url('/produit/' . $product->slug()) ?>" tabindex="-1">
                <?= ProductArt::product($product, 'hero__art') ?>
                <span class="hero__tile-label"><?= $v->e($product->name()) ?><b><?= $v->money($product->price()) ?></b></span>
            </a>
            <?php endforeach; ?>
            <span class="hero__blob hero__blob--pink"></span>
            <span class="hero__blob hero__blob--blue"></span>
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Deux univers', 'title' => 'Par où commencer ?', 'text' => 'Chaque univers a ses rayons, ses teintes et ses conseils. Entrez par celui qui vous fait envie.']) ?>
        <div class="universes">
            <?php foreach ($categories as $category) :
                $bounds = $priceBounds[$category->slug()] ?? ['min' => 0, 'max' => 0];
                $isWatch = $category->slug() === 'montres';
                ?>
            <a class="<?= $v->classes(['universe' => true, 'universe--pink' => !$isWatch, 'universe--blue' => $isWatch]) ?>" href="<?= $v->url('/boutique/' . $category->slug()) ?>" data-reveal>
                <div class="universe__art"><?= ProductArt::render($isWatch ? 'watch-round' : 'lipstick', $isWatch ? '#bcdcf5' : '#e7a2b2', $isWatch ? 'pink' : 'blue', 'universe__svg') ?></div>
                <div class="universe__body">
                    <p class="kicker"><?= $v->e($category->name()) ?></p>
                    <h3><?= $v->e($category->title()) ?></h3>
                    <p><?= $v->e($category->intro()) ?></p>
                    <ul class="universe__subs">
                        <?php foreach ($category->subcategories() as $sub) : ?><li><?= $v->e($sub['name']) ?></li><?php endforeach; ?>
                    </ul>
                    <span class="universe__price">De <?= $v->money($bounds['min']) ?> à <?= $v->money($bounds['max']) ?></span>
                    <span class="link">Entrer <svg aria-hidden="true"><use href="#i-arrow"/></svg></span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--tint">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Les préférés', 'title' => 'Ce que vous commandez le plus', 'text' => 'Les produits qui reviennent dans presque toutes les commandes, et les avis qui vont avec.', 'linkHref' => $v->url('/boutique'), 'linkLabel' => 'Toute la boutique']) ?>
        <div class="grid grid--products">
            <?php foreach ($featured as $product) : ?>
            <?= $v->partial('partials/product-card', ['product' => $product]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="wrap">
        <div class="promise" data-reveal>
            <div class="promise__item"><svg aria-hidden="true"><use href="#i-leaf"/></svg><h3>Formules soignées</h3><p>Sans paraben ni huile minérale, jamais testées sur les animaux, la plupart vegan. Et des teintes qui vont vraiment à toutes.</p></div>
            <div class="promise__item"><svg aria-hidden="true"><use href="#i-clock"/></svg><h3>Montres fiables</h3><p>Mouvements à quartz japonais ou suisses, verre traité ou saphir, étanchéité testée. Garanties deux ans, pile offerte.</p></div>
            <div class="promise__item"><svg aria-hidden="true"><use href="#i-gift"/></svg><h3>Prêt à offrir</h3><p>Chaque montre arrive dans son écrin, chaque coffret est fermé d'un ruban. Ajoutez un message : il est écrit à la main.</p></div>
            <div class="promise__item"><svg aria-hidden="true"><use href="#i-chat"/></svg><h3>Une conseillère disponible</h3><p>Une hésitation sur une teinte ou une taille ? Léa répond en ligne, tout de suite, et l'équipe sous 24 heures.</p></div>
        </div>
    </div>
</section>

<section class="section section--pink">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Inspirations', 'title' => 'Un look complet, étape par étape', 'text' => 'Pour tous les jours, les cours, une soirée ou une cérémonie : les produits, l\'ordre, le geste, et la montre qui va avec.', 'linkHref' => $v->url('/inspirations'), 'linkLabel' => 'Toutes les inspirations']) ?>
        <div class="grid grid--looks">
            <?php foreach ($looks as $look) : ?>
            <?= $v->partial('partials/look-card', ['look' => $look]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php if ($newProducts !== []) : ?>
<section class="section">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Nouveautés', 'title' => 'Tout juste arrivés', 'linkHref' => $v->url('/boutique', ['badge' => 'nouveau']), 'linkLabel' => 'Voir les nouveautés']) ?>
        <div class="grid grid--products">
            <?php foreach ($newProducts as $product) : ?>
            <?= $v->partial('partials/product-card', ['product' => $product]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section section--tint">
    <div class="wrap split">
        <div data-reveal>
            <p class="kicker">Questions fréquentes</p>
            <h2 class="h2">Ce que l'on nous demande avant de commander</h2>
            <p class="lead">Livraison, paiement sur facture, retours, choix de teinte : les réponses courtes. La conseillère en ligne a les autres.</p>
            <a class="btn btn--soft" href="<?= $v->url('/conseillere') ?>">Poser une question à Léa <svg aria-hidden="true"><use href="#i-arrow"/></svg></a>
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
        <div class="cta" data-reveal>
            <div>
                <p class="kicker kicker--light">Première commande</p>
                <h2>−10 % avec le code <span class="code">BIENVENUE10</span></h2>
                <p>Valable sur tout le maquillage et toutes les montres, cumulable avec la livraison offerte dès CHF 80.</p>
            </div>
            <a class="btn btn--light btn--lg" href="<?= $v->url('/boutique') ?>">Commencer mes achats <svg aria-hidden="true"><use href="#i-arrow"/></svg></a>
        </div>
    </div>
</section>
