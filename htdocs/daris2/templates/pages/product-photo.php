<?php
/**
 * Photo produit dans le gabarit boutique (liens de retour visibles).
 *
 * @var \Daris\View\View $v
 * @var \Daris\Catalog\Product $product
 * @var \Daris\Catalog\Category|null $category
 */
$photoAlt = $product->brand() . ' — ' . $product->name();
?>
<section class="section product-photo-page">
    <div class="wrap">
        <nav class="crumbs" aria-label="<?= $v->e($v->t('nav.breadcrumb', 'Fil d\'Ariane')) ?>">
            <ol>
                <li><a href="<?= $v->url('/') ?>"><?= $v->e($v->t('nav.home', 'Accueil')) ?></a></li>
                <li><a href="<?= $v->url('/boutique') ?>"><?= $v->e($v->t('nav.shop', 'Boutique')) ?></a></li>
                <li><a href="<?= $v->url('/produit/' . $product->slug()) ?>"><?= $v->e($product->name()) ?></a></li>
                <li><span aria-current="page"><?= $v->e($v->t('photo.title', 'Photo')) ?></span></li>
            </ol>
        </nav>

        <div class="product-photo-page__actions">
            <a class="btn btn--primary" href="<?= $v->url('/produit/' . $product->slug()) ?>"><svg aria-hidden="true"><use href="#i-arrow-left"/></svg> <?= $v->e($v->t('photo.back_product', 'Retour au produit')) ?></a>
            <a class="btn btn--soft" href="<?= $v->url('/boutique') ?>"><?= $v->e($v->t('photo.back_shop', 'Retour à la boutique')) ?></a>
        </div>

        <figure class="product-photo-page__frame">
            <?php if ($product->hasPhoto()) : ?>
            <img class="product-photo-page__img" src="<?= $v->asset((string) $product->photo()['file']) ?>" alt="<?= $v->e($photoAlt) ?>" decoding="async" draggable="false">
            <?php else : ?>
            <?= \Daris\View\ProductArt::product($product, 'product-photo-page__art') ?>
            <?php endif; ?>
            <figcaption class="product-photo-page__caption"><?= $v->e($photoAlt) ?></figcaption>
        </figure>

        <?= $v->partial('partials/photo-credit', ['product' => $product]) ?>
    </div>
</section>
