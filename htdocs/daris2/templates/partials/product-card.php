<?php
/**
 * Carte produit (grilles, suggestions).
 *
 * @var \Daris\View\View $v
 * @var \Daris\Catalog\Product $product
 * @var bool $compact
 */
use Daris\View\ProductArt;

$compact = $compact ?? false;
$href = $v->url('/produit/' . $product->slug());
?>
<article class="<?= $v->classes(['card' => true, 'card--compact' => $compact, 'card--watch' => $product->isWatch()]) ?>" data-reveal>
    <a class="card__media" href="<?= $href ?>" tabindex="-1" aria-hidden="true">
        <?= $v->partial('partials/product-media', ['product' => $product, 'class' => 'card__art']) ?>
        <?php if ($product->badge() !== null) : ?>
        <span class="badge badge--<?= $v->e((string) $product->badge()) ?>"><?= $v->e($product->badgeLabel()) ?><?= $product->discountPercent() > 0 ? ' −' . $product->discountPercent() . ' %' : '' ?></span>
        <?php endif; ?>
        <?php if ($product->hasVariants() && !$compact) : ?>
        <span class="card__swatches" aria-hidden="true">
            <?php foreach (array_slice($product->variants(), 0, 5) as $variant) : ?>
            <svg viewBox="0 0 10 10"><circle cx="5" cy="5" r="5" fill="<?= $v->e($variant->color()) ?>"/></svg>
            <?php endforeach; ?>
            <?php if (count($product->variants()) > 5) : ?><b>+<?= count($product->variants()) - 5 ?></b><?php endif; ?>
        </span>
        <?php endif; ?>
    </a>
    <button class="card__fav" type="button" data-fav="<?= $v->e($product->slug()) ?>" aria-pressed="false" aria-label="Ajouter <?= $v->e($product->name()) ?> aux favoris">
        <svg aria-hidden="true"><use href="#i-heart"/></svg>
    </button>
    <div class="card__body">
        <p class="card__kicker"><span class="card__brand"><?= $v->e($product->brand()) ?></span><?php if ($product->rating() > 0) : ?> · <span class="stars" aria-label="Note <?= $v->e((string) $product->rating()) ?> sur 5">★ <?= $v->e(number_format($product->rating(), 1)) ?></span><?php endif; ?></p>
        <h3 class="card__title"><a href="<?= $href ?>"><?= $v->e($product->name()) ?></a></h3>
        <?php if (!$compact) : ?>
        <p class="card__short"><?= $v->e($product->short()) ?></p>
        <?php endif; ?>
        <div class="card__foot">
            <p class="price"><span class="price__now"><?= $v->money($product->price()) ?></span><?php if ($product->oldPrice() !== null) : ?> <s class="price__old"><?= $v->money($product->oldPrice()) ?></s><?php endif; ?></p>
            <?php if ($product->hasVariants()) : ?>
            <a class="btn btn--ghost btn--sm" href="<?= $href ?>">Choisir</a>
            <?php else : ?>
            <form action="<?= $v->url('/panier/ajouter') ?>" method="post" data-add-to-cart>
                <?= $v->csrfField() ?>
                <input type="hidden" name="product_id" value="<?= $product->id() ?>">
                <input type="hidden" name="quantity" value="1">
                <button class="btn btn--ghost btn--sm" type="submit" aria-label="Ajouter <?= $v->e($product->name()) ?> au panier">Ajouter</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</article>
