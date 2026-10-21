<?php
/**
 * @var \Daris\View\View $v
 * @var \Daris\Catalog\Look $look
 * @var list<\Daris\Catalog\Product> $products
 * @var int $total
 * @var list<\Daris\Catalog\Look> $others
 */
use Daris\View\ProductArt;

$main = $look->palette()[0] ?? '#f2b9cc';
?>
<?= $v->partial('partials/page-head', [
    'kicker' => $look->occasion() . ' · ' . $look->level(),
    'title' => $look->title(),
    'lead' => $look->intro(),
    'crumbs' => [[$v->url('/inspirations'), 'Inspirations'], ['', $look->title()]],
    'tone' => 'pink',
]) ?>

<section class="section section--flush">
    <div class="wrap look-detail">
        <div class="look-detail__hero" data-reveal>
            <?= ProductArt::render($look->art(), $main, 'blue', 'look-detail__art') ?>
            <ul class="look-meta">
                <li><svg aria-hidden="true"><use href="#i-clock"/></svg><?= $v->e($look->duration()) ?></li>
                <li><svg aria-hidden="true"><use href="#i-user"/></svg><?= $v->e($look->level()) ?></li>
                <li><strong><?= $v->money($total) ?></strong> pour tout le look</li>
            </ul>
        </div>

        <ol class="look-steps">
            <?php foreach ($look->steps() as $i => $step) : ?>
            <li data-reveal>
                <span class="look-steps__num"><?= $i + 1 ?></span>
                <div>
                    <h2 class="h3"><?= $v->e($step['title']) ?></h2>
                    <p><?= $v->e($step['text']) ?></p>
                </div>
            </li>
            <?php endforeach; ?>
        </ol>

        <div class="look-products">
            <h2 class="h2">Les produits du look</h2>
            <div class="grid grid--products">
                <?php foreach ($products as $product) : ?>
                <?= $v->partial('partials/product-card', ['product' => $product]) ?>
                <?php endforeach; ?>
            </div>
            <form class="look-add-all" method="post" action="<?= $v->url('/inspirations/' . $look->slug() . '/panier') ?>">
                <?= $v->csrfField() ?>
                <button class="btn btn--primary btn--lg" type="submit">Tout ajouter au panier (<?= $v->money($total) ?>)</button>
                <p class="hint">Les teintes par défaut sont sélectionnées ; vous pourrez les ajuster dans le panier.</p>
            </form>
        </div>
    </div>
</section>

<?php if ($others !== []) : ?>
<section class="section section--tint">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Suite', 'title' => 'D\'autres inspirations']) ?>
        <div class="grid grid--looks">
            <?php foreach ($others as $other) : ?>
            <?= $v->partial('partials/look-card', ['look' => $other]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
