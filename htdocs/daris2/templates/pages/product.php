<?php
/**
 * Fiche produit.
 *
 * @var \Daris\View\View $v
 * @var \Daris\Catalog\Product $product
 * @var \Daris\Catalog\Category|null $category
 * @var list<\Daris\Catalog\Product> $related
 * @var list<\Daris\Catalog\Look> $looks
 * @var array<string, string|int> $app
 */
use Daris\View\ProductArt;

$variants = $product->variants();
$first = $variants[0] ?? null;
$subName = $category?->subcategoryName($product->subcategory()) ?? '';
?>
<section class="product">
    <div class="wrap">
        <nav class="crumbs" aria-label="Fil d'Ariane">
            <ol>
                <li><a href="<?= $v->url('/') ?>">Accueil</a></li>
                <li><a href="<?= $v->url('/boutique') ?>">Boutique</a></li>
                <?php if ($category !== null) : ?><li><a href="<?= $v->url('/boutique/' . $category->slug()) ?>"><?= $v->e($category->name()) ?></a></li><?php endif; ?>
                <?php if ($subName !== '' && $category !== null) : ?><li><a href="<?= $v->url('/boutique/' . $category->slug(), ['sous' => $product->subcategory()]) ?>"><?= $v->e($subName) ?></a></li><?php endif; ?>
                <li><span aria-current="page"><?= $v->e($product->name()) ?></span></li>
            </ol>
        </nav>

        <div class="product__layout">
            <div class="product__gallery" data-gallery>
                <div class="product__stage">
                    <?= ProductArt::product($product, 'product__art') ?>
                    <?php if ($product->badge() !== null) : ?>
                    <span class="badge badge--<?= $v->e((string) $product->badge()) ?>"><?= $v->e($product->badgeLabel()) ?><?= $product->discountPercent() > 0 ? ' −' . $product->discountPercent() . ' %' : '' ?></span>
                    <?php endif; ?>
                    <?php if ($first !== null) : ?>
                    <span class="product__stage-variant" data-variant-name><?= $v->e($first->name()) ?></span>
                    <?php endif; ?>
                </div>
                <ul class="product__highlights">
                    <?php foreach (array_slice($product->tags(), 0, 3) as $tag) : ?>
                    <li><svg aria-hidden="true"><use href="#i-check"/></svg><?= $v->e($tag) ?></li>
                    <?php endforeach; ?>
                    <li><svg aria-hidden="true"><use href="#i-truck"/></svg>Expédié sous 24 h</li>
                </ul>
            </div>

            <div class="product__info">
                <p class="kicker"><?= $v->e($category?->name() ?? '') ?><?= $subName !== '' ? ' · ' . $v->e($subName) : '' ?></p>
                <h1 class="product__title"><?= $v->e($product->name()) ?></h1>
                <p class="product__rating"><span class="stars" aria-hidden="true"><?= str_repeat('★', (int) round($product->rating())) . str_repeat('☆', 5 - (int) round($product->rating())) ?></span> <span><?= $v->e(number_format($product->rating(), 1)) ?> · <?= $product->reviews() ?> avis</span></p>
                <p class="product__short"><?= $v->e($product->short()) ?></p>

                <p class="price price--lg">
                    <span class="price__now"><?= $v->money($product->price()) ?></span>
                    <?php if ($product->oldPrice() !== null) : ?><s class="price__old"><?= $v->money($product->oldPrice()) ?></s><?php endif; ?>
                    <small>TVA 8.1 % incluse</small>
                </p>

                <form class="buy" action="<?= $v->url('/panier/ajouter') ?>" method="post" data-add-to-cart data-buy>
                    <?= $v->csrfField() ?>
                    <input type="hidden" name="product_id" value="<?= $product->id() ?>">

                    <?php if ($variants !== []) : ?>
                    <fieldset class="variants">
                        <legend><?= $v->e($product->variantLabel()) ?> : <strong data-variant-label><?= $v->e($first?->name() ?? '') ?></strong></legend>
                        <div class="variants__list">
                            <?php foreach ($variants as $i => $variant) : ?>
                            <label class="swatch" title="<?= $v->e($variant->name()) ?>">
                                <input type="radio" name="variant" value="<?= $v->e($variant->id()) ?>" data-variant-name="<?= $v->e($variant->name()) ?>" <?= $i === 0 ? 'checked' : '' ?> required>
                                <svg viewBox="0 0 32 32" aria-hidden="true"><circle cx="16" cy="16" r="14" fill="<?= $v->e($variant->color()) ?>"/></svg>
                                <span class="sr-only"><?= $v->e($variant->name()) ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                    <?php endif; ?>

                    <div class="buy__row">
                        <div class="qty" data-qty>
                            <button type="button" data-qty-minus aria-label="Diminuer la quantité"><svg aria-hidden="true"><use href="#i-minus"/></svg></button>
                            <label class="sr-only" for="quantity">Quantité</label>
                            <input id="quantity" type="number" name="quantity" value="1" min="1" max="10" inputmode="numeric">
                            <button type="button" data-qty-plus aria-label="Augmenter la quantité"><svg aria-hidden="true"><use href="#i-plus"/></svg></button>
                        </div>
                        <?php if ($product->isInStock()) : ?>
                        <button class="btn btn--primary btn--lg buy__submit" type="submit"><svg aria-hidden="true"><use href="#i-bag"/></svg> Ajouter au panier</button>
                        <?php else : ?>
                        <button class="btn btn--primary btn--lg" type="button" disabled>Bientôt de retour</button>
                        <?php endif; ?>
                    </div>
                    <p class="buy__stock">
                        <?php if ($product->isLowStock()) : ?><span class="dot dot--warn"></span>Plus que <?= $product->stock() ?> en stock
                        <?php elseif ($product->isInStock()) : ?><span class="dot dot--ok"></span>En stock, expédié sous 24 h
                        <?php else : ?><span class="dot dot--off"></span>Rupture temporaire<?php endif; ?>
                    </p>
                </form>

                <ul class="product__reassure">
                    <li><svg aria-hidden="true"><use href="#i-truck"/></svg>Livraison offerte dès <?= $v->money((int) $app['freeShippingFrom']) ?>, sinon CHF 7.00</li>
                    <li><svg aria-hidden="true"><use href="#i-refresh"/></svg>30 jours pour changer d'avis, retour gratuit</li>
                    <?php if ($product->isWatch()) : ?><li><svg aria-hidden="true"><use href="#i-shield"/></svg>Garantie 2 ans, écrin inclus, pile offerte</li>
                    <?php else : ?><li><svg aria-hidden="true"><use href="#i-leaf"/></svg>Jamais testé sur les animaux, échange de teinte gratuit</li><?php endif; ?>
                </ul>

                <div class="tabs" data-tabs>
                    <div class="tabs__list" role="tablist" aria-label="Informations produit">
                        <button class="tabs__tab is-active" role="tab" id="tab-desc" aria-controls="panel-desc" aria-selected="true" type="button">Description</button>
                        <button class="tabs__tab" role="tab" id="tab-details" aria-controls="panel-details" aria-selected="false" tabindex="-1" type="button"><?= $product->isWatch() ? 'Caractéristiques' : 'Composition & infos' ?></button>
                        <button class="tabs__tab" role="tab" id="tab-care" aria-controls="panel-care" aria-selected="false" tabindex="-1" type="button"><?= $product->isWatch() ? 'Entretien' : 'Application' ?></button>
                    </div>
                    <div class="tabs__panel prose" role="tabpanel" id="panel-desc" aria-labelledby="tab-desc"><?= $v->paragraphs($product->description()) ?></div>
                    <div class="tabs__panel" role="tabpanel" id="panel-details" aria-labelledby="tab-details" hidden>
                        <ul class="details">
                            <?php foreach ($product->details() as $detail) : ?><li><?= $v->e($detail) ?></li><?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="tabs__panel prose" role="tabpanel" id="panel-care" aria-labelledby="tab-care" hidden>
                        <?php if ($product->isWatch()) : ?>
                        <p>Essuyez le boîtier et le bracelet avec un chiffon doux après le sport ou la baignade. Ne manipulez jamais la couronne sous l'eau. Rangez la montre à l'abri du soleil direct et des aimants (haut-parleurs, fermoirs de sac).</p>
                        <p>Une pile dure deux à trois ans ; son remplacement est offert pendant la garantie. Les bracelets en cuir n'aiment pas l'eau : préférez le milanais ou le silicone pour l'été.</p>
                        <?php else : ?>
                        <p>Appliquez sur une peau propre et hydratée. Pour les produits en crème, travaillez au doigt par petites touches ; pour les poudres, au pinceau, en tapotant plutôt qu'en frottant. Fermez bien le produit après usage.</p>
                        <p>La durée de conservation après ouverture est indiquée sur l'emballage (symbole du petit pot). En cas de doute sur une teinte, la conseillère en ligne vous aide, et l'échange est gratuit.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if ($looks !== []) : ?>
<section class="section section--pink">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Dans quel look ?', 'title' => 'Ce produit en situation', 'linkHref' => $v->url('/inspirations'), 'linkLabel' => 'Toutes les inspirations']) ?>
        <div class="grid grid--looks">
            <?php foreach ($looks as $look) : ?>
            <?= $v->partial('partials/look-card', ['look' => $look]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if ($related !== []) : ?>
<section class="section">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'Vous aimerez aussi', 'title' => 'Dans le même esprit', 'linkHref' => $category !== null ? $v->url('/boutique/' . $category->slug()) : $v->url('/boutique'), 'linkLabel' => 'Voir le rayon']) ?>
        <div class="grid grid--products">
            <?php foreach ($related as $item) : ?>
            <?= $v->partial('partials/product-card', ['product' => $item]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
