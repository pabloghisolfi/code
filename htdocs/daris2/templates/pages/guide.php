<?php
/**
 * @var \Daris\View\View $v
 * @var \Daris\Catalog\Product|null $foundation
 * @var list<\Daris\Catalog\Product> $watches
 * @var list<\Daris\Catalog\Product> $starterKit
 */
?>
<?= $v->partial('partials/page-head', ['kicker' => 'Le guide', 'title' => 'Conseils maquillage & montres', 'lead' => 'Trouver sa teinte, se maquiller en cinq minutes, choisir une montre à son poignet et en prendre soin : tout ce qu\'il faut savoir, sans jargon.', 'crumbs' => [['', 'Guide']]]) ?>

<section class="section" id="sous-ton">
    <div class="wrap split">
        <div data-reveal>
            <p class="kicker">Teint</p>
            <h2 class="h2">Rosé ou doré ?</h2>
            <p class="lead">Regardez les veines à l'intérieur de votre poignet, à la lumière du jour.</p>
            <ul class="guide-list">
                <li><strong>Veines bleutées ou violacées</strong> → sous-ton rosé. Choisissez les teintes « R » du fond de teint Voile de soie.</li>
                <li><strong>Veines verdâtres</strong> → sous-ton doré. Choisissez les teintes « D ».</li>
                <li><strong>Les deux ?</strong> Prenez la teinte la plus claire des deux : elle se fond mieux.</li>
            </ul>
            <?php if ($foundation !== null) : ?>
            <a class="btn btn--primary" href="<?= $v->url('/produit/' . $foundation->slug()) ?>">Voir Voile de soie</a>
            <?php endif; ?>
        </div>
        <div class="guide-card" data-reveal>
            <h3 class="h3">Routine express (5 min)</h3>
            <ol class="steps">
                <li><strong>Teint</strong> — Une noisette de fond de teint au doigt, du centre vers l'extérieur.</li>
                <li><strong>Cernes</strong> — Trois points de correcteur, tapotés à l'annulaire.</li>
                <li><strong>Joues</strong> — Une touche de blush crème sur les pommettes.</li>
                <li><strong>Regard</strong> — Un passage de mascara sur les cils du haut.</li>
                <li><strong>Lèvres</strong> — Baume teinté ou rouge satiné selon l'occasion.</li>
            </ol>
        </div>
    </div>
</section>

<section class="section section--pink" id="taille-montre">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Montres', 'title' => 'Quelle taille de boîtier ?', 'text' => 'Mesurez votre tour de poignet avec un mètre ruban, juste au-dessus de l\'os, sans serrer.', 'center' => true]) ?>
        <div class="size-calculator" data-watch-size data-reveal>
            <label for="wrist">Tour de poignet (cm)</label>
            <input id="wrist" type="range" min="13" max="20" step="0.5" value="16">
            <output id="wrist-out" for="wrist">16 cm</output>
            <p class="size-result" data-size-result>Boîtier conseillé : <strong>28 à 34 mm</strong></p>
            <p class="hint">Exemples : Lune 32 mm, Brume 26 mm, Minuit 36 mm, Horizon 38 mm.</p>
        </div>
        <div class="grid grid--products">
            <?php foreach (array_slice($watches, 0, 4) as $product) : ?>
            <?= $v->partial('partials/product-card', ['product' => $product, 'compact' => true]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section" id="entretien">
    <div class="wrap split">
        <div class="prose" data-reveal>
            <h2 class="h2">Entretenir sa montre</h2>
            <p>Essuyez le boîtier après le sport. Ne tournez jamais la couronne sous l'eau. Rangez-la à l'abri des aimants (haut-parleurs, fermoirs de sac).</p>
            <p><strong>3 ATM</strong> : pluie et éclaboussures. <strong>5 ATM</strong> : douche, baignade sans plongeon. <strong>10 ATM</strong> : natation.</p>
        </div>
        <div class="prose" data-reveal>
            <h2 class="h2">Conserver le maquillage</h2>
            <p>Fermez bien les produits. Notez la date d'ouverture (symbole du petit pot). Mascara : 6 mois. Gloss : 12 mois. Poudres : 24 mois.</p>
        </div>
    </div>
</section>

<?php if ($starterKit !== []) : ?>
<section class="section section--tint">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Pour débuter', 'title' => 'Quatre produits suffisent', 'linkHref' => $v->url('/exemples/matin-frais'), 'linkLabel' => 'Voir le look Matin frais']) ?>
        <div class="grid grid--products">
            <?php foreach ($starterKit as $product) : ?>
            <?= $v->partial('partials/product-card', ['product' => $product]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
