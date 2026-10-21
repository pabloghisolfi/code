<?php
/**
 * @var \Daris\View\View $v
 * @var list<\Daris\Catalog\Look> $looks
 */
?>
<?= $v->partial('partials/page-head', ['kicker' => 'Exemples', 'title' => 'Looks & idées', 'lead' => 'Des combinaisons prêtes à porter : produits, ordre d\'application, et la montre qui complète le style.', 'crumbs' => [['', 'Exemples']], 'tone' => 'pink']) ?>

<section class="section section--flush">
    <div class="wrap">
        <div class="grid grid--looks">
            <?php foreach ($looks as $look) : ?>
            <?= $v->partial('partials/look-card', ['look' => $look]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
