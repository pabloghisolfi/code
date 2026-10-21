<?php
/**
 * Panier.
 *
 * @var \Daris\View\View $v
 * @var list<\Daris\Cart\CartItem> $items
 * @var \Daris\Cart\CartTotals $totals
 * @var \Daris\Cart\PromoCode|null $promo
 * @var int $remainingForFreeShipping
 * @var list<\Daris\Catalog\Product> $suggestions
 * @var int $maxQuantity
 * @var array<string, string|int> $app
 */
use Daris\View\ProductArt;

$freeFrom = (int) $app['freeShippingFrom'];
$progress = $freeFrom > 0 ? min(100, (int) round(($freeFrom - $remainingForFreeShipping) / $freeFrom * 100)) : 100;
?>
<?= $v->partial('partials/page-head', ['kicker' => $v->t('cart.title', 'Panier'), 'title' => $items === [] ? $v->t('cart.empty_title', 'Votre panier est vide') : $v->t('cart.title', 'Votre panier'), 'lead' => $items === [] ? $v->t('cart.empty_lead', 'Il n\'attend que vous.') : $v->t('cart.lead', 'Vérifiez les quantités puis passez à la commande.'), 'crumbs' => [['', $v->t('cart.title', 'Panier')]]]) ?>

<section class="section section--flush">
    <div class="wrap">
        <?php if ($items === []) : ?>
        <div class="empty" data-reveal>
            <div class="actions"><a class="btn btn--primary btn--lg" href="<?= $v->url('/boutique/maquillage') ?>">Voir le maquillage</a><a class="btn btn--soft btn--lg" href="<?= $v->url('/boutique/montres') ?>">Voir les montres</a></div>
        </div>
        <?php else : ?>
        <div class="cart">
            <div class="cart__items">
                <div class="shipbar" data-shipbar>
                    <?php if ($remainingForFreeShipping > 0) : ?>
                    <p>Plus que <strong data-shipbar-remaining><?= $v->money($remainingForFreeShipping) ?></strong> pour la livraison offerte.</p>
                    <?php else : ?>
                    <p><svg aria-hidden="true"><use href="#i-check"/></svg> La livraison Economy est offerte.</p>
                    <?php endif; ?>
                    <div class="shipbar__track"><i class="shipbar__fill" data-shipbar-fill data-progress="<?= $progress ?>"></i></div>
                </div>

                <ul class="cart__list">
                    <?php foreach ($items as $item) : $product = $item->product(); ?>
                    <li class="cart-line" data-cart-line data-key="<?= $v->e($item->key()) ?>">
                        <a class="cart-line__media" href="<?= $v->url('/produit/' . $product->slug()) ?>"><?= $v->partial('partials/product-media', ['product' => $product, 'class' => 'cart-line__art']) ?></a>
                        <div class="cart-line__body">
                            <p class="kicker"><?= $product->isWatch() ? 'Montre' : 'Maquillage' ?></p>
                            <h2 class="cart-line__title"><a href="<?= $v->url('/produit/' . $product->slug()) ?>"><?= $v->e($product->name()) ?></a></h2>
                            <?php if ($item->variant() !== null) : ?>
                            <p class="cart-line__variant"><svg viewBox="0 0 10 10" aria-hidden="true"><circle cx="5" cy="5" r="5" fill="<?= $v->e($item->variant()->color()) ?>"/></svg> <?= $v->e($product->variantLabel()) ?> : <?= $v->e($item->variant()->name()) ?></p>
                            <?php endif; ?>
                            <p class="cart-line__unit"><?= $v->money($item->unitPrice()) ?> l'unité</p>
                        </div>
                        <form class="cart-line__qty" action="<?= $v->url('/panier/modifier') ?>" method="post" data-cart-update>
                            <?= $v->csrfField() ?>
                            <input type="hidden" name="key" value="<?= $v->e($item->key()) ?>">
                            <div class="qty" data-qty>
                                <button type="button" data-qty-minus aria-label="Diminuer"><svg aria-hidden="true"><use href="#i-minus"/></svg></button>
                                <label class="sr-only" for="qty-<?= $v->e($item->key()) ?>">Quantité</label>
                                <input id="qty-<?= $v->e($item->key()) ?>" type="number" name="quantity" value="<?= $item->quantity() ?>" min="0" max="<?= $maxQuantity ?>" inputmode="numeric" data-qty-input>
                                <button type="button" data-qty-plus aria-label="Augmenter"><svg aria-hidden="true"><use href="#i-plus"/></svg></button>
                            </div>
                            <noscript><button class="btn btn--ghost btn--sm" type="submit">Mettre à jour</button></noscript>
                        </form>
                        <p class="cart-line__total" data-line-total><?= $v->money($item->total()) ?></p>
                        <form class="cart-line__remove" action="<?= $v->url('/panier/supprimer') ?>" method="post" data-cart-remove>
                            <?= $v->csrfField() ?>
                            <input type="hidden" name="key" value="<?= $v->e($item->key()) ?>">
                            <button type="submit" aria-label="Retirer <?= $v->e($item->label()) ?>"><svg aria-hidden="true"><use href="#i-close"/></svg> Retirer</button>
                        </form>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <p class="cart__continue"><a class="link" href="<?= $v->url('/boutique') ?>"><svg aria-hidden="true"><use href="#i-arrow-left"/></svg> Continuer mes achats</a></p>
            </div>

            <aside class="summary" data-summary>
                <h2 class="summary__title">Récapitulatif</h2>
                <dl class="summary__rows">
                    <div><dt>Sous-total</dt><dd data-summary-subtotal><?= $v->money($totals->subtotal()) ?></dd></div>
                    <?php if ($totals->discount() > 0) : ?>
                    <div class="is-discount"><dt><?= $v->e((string) $totals->promoLabel()) ?></dt><dd data-summary-discount>− <?= $v->money($totals->discount()) ?></dd></div>
                    <?php endif; ?>
                    <div><dt><?= $v->e($totals->shippingLabel()) ?></dt><dd data-summary-shipping><?= $totals->shipping() === 0 ? 'Offerte' : $v->money($totals->shipping()) ?></dd></div>
                    <?php if ($totals->giftWrap() > 0) : ?>
                    <div><dt>Emballage cadeau</dt><dd><?= $v->money($totals->giftWrap()) ?></dd></div>
                    <?php endif; ?>
                    <div class="summary__total"><dt>Total</dt><dd data-summary-total><?= $v->money($totals->total()) ?></dd></div>
                    <div class="summary__vat"><dt>dont TVA <?= $v->e(rtrim(rtrim(number_format($totals->vatRate(), 1), '0'), '.')) ?> %</dt><dd><?= $v->money($totals->vatIncluded()) ?></dd></div>
                </dl>

                <details class="promo" <?= $promo !== null ? 'open' : '' ?>>
                    <summary>J'ai un code</summary>
                    <?php if ($promo !== null) : ?>
                    <form class="promo__form" action="<?= $v->url('/panier/code') ?>" method="post">
                        <?= $v->csrfField() ?>
                        <input type="hidden" name="action" value="remove">
                        <p class="promo__applied"><svg aria-hidden="true"><use href="#i-check"/></svg> <strong><?= $v->e($promo->code()) ?></strong> appliqué</p>
                        <button class="link" type="submit">Retirer</button>
                    </form>
                    <?php else : ?>
                    <form class="promo__form" action="<?= $v->url('/panier/code') ?>" method="post">
                        <?= $v->csrfField() ?>
                        <label class="sr-only" for="code">Code promotionnel</label>
                        <input id="code" type="text" name="code" placeholder="BIENVENUE10" maxlength="30" autocomplete="off" autocapitalize="characters">
                        <button class="btn btn--ghost btn--sm" type="submit">Appliquer</button>
                    </form>
                    <?php endif; ?>
                </details>

                <a class="btn btn--primary btn--lg btn--block" href="<?= $v->url('/commande') ?>">Passer la commande <svg aria-hidden="true"><use href="#i-arrow"/></svg></a>
                <ul class="summary__trust">
                    <li><svg aria-hidden="true"><use href="#i-lock"/></svg>Paiement sécurisé : facture, TWINT, carte</li>
                    <li><svg aria-hidden="true"><use href="#i-refresh"/></svg>30 jours pour changer d'avis</li>
                </ul>
            </aside>
        </div>
        <?php endif; ?>
    </div>
</section>

<section class="section section--tint">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => $items === [] ? 'Pour commencer' : 'Pour compléter', 'title' => $items === [] ? 'Nos préférés du moment' : 'Souvent ajoutés ensemble', 'linkHref' => $v->url('/boutique'), 'linkLabel' => 'Toute la boutique']) ?>
        <div class="grid grid--products">
            <?php foreach ($suggestions as $product) : ?>
            <?= $v->partial('partials/product-card', ['product' => $product]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
