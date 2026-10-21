<?php
/**
 * Visuel produit : photo sous licence libre ou illustration SVG de repli.
 *
 * @var \Daris\View\View $v
 * @var \Daris\Catalog\Product $product
 * @var string $class
 */
use Daris\View\ProductArt;

$class = $class ?? 'art';
$alt = $product->brand() . ' — ' . $product->name();
if ($product->hasPhoto()) {
    $photo = $product->photo();
    ?>
<img class="<?= $v->e($class) ?> product-photo" src="<?= $v->asset((string) $photo['file']) ?>" alt="<?= $v->e($alt) ?>" loading="lazy" decoding="async" width="800" height="800" draggable="false">
    <?php
} else {
    echo ProductArt::product($product, $class);
}
