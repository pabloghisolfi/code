<?php
/**
 * @var \Daris\View\View $v
 * @var list<\Daris\Catalog\Category> $categories
 * @var list<\Daris\Catalog\Product> $bestSellers
 */
?>
<?= $v->partial('partials/page-head', ['kicker' => 'À propos', 'title' => 'Azurine, sélection multimarque', 'lead' => 'Azurine est une boutique suisse indépendante qui propose les plus grandes maisons de maquillage et d\'horlogerie à des prix CHF transparents. Nous ne sommes pas les boutiques officielles des manufactures : nous sélectionnons, conseillons et livrons avec soin.', 'crumbs' => [['', 'À propos']]]) ?>

<section class="section">
    <div class="wrap prose wide" data-reveal>
        <p>Nous sélectionnons des formules confortables, des teintes qui vont à toutes les carnations, et des montres fiables que l'on garde des années. Chaque prix est affiché en francs suisses, taxes comprises, sans surprise à la caisse.</p>
        <p>L'équipe répond depuis Lausanne, du lundi au vendredi. La conseillère Léa est disponible en ligne pour les questions de teinte et de taille ; pour le reste, une personne humaine répond sous 24 heures ouvrées.</p>
        <h2 class="h2">Ce que nous promettons</h2>
        <ul class="guide-list">
            <li>Des ingrédients choisis, jamais testés sur les animaux</li>
            <li>Des montres garanties deux ans, trois ans pour la Minuit Swiss Made</li>
            <li>Livraison offerte dès CHF 80 en Suisse et au Liechtenstein</li>
            <li>30 jours pour retourner un article, sans frais</li>
        </ul>
    </div>
</section>

<section class="section section--tint">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Best-sellers', 'title' => 'Ce que vous aimez le plus']) ?>
        <div class="grid grid--products">
            <?php foreach ($bestSellers as $product) : ?>
            <?= $v->partial('partials/product-card', ['product' => $product]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
