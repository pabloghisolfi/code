<?php
/**
 * @var \Daris\View\View $v
 * @var list<\Daris\Cart\CartItem> $items
 * @var \Daris\Cart\CartTotals $totals
 * @var array<string, string> $old
 * @var array<string, string> $errors
 * @var array<string, string> $cantons
 * @var array<string, mixed> $shippingMethods
 * @var array<string, mixed> $paymentMethods
 * @var int $giftWrapPrice
 * @var int $freeShippingFrom
 * @var string $selectedShipping
 * @var bool $giftWrap
 */
use Daris\View\ProductArt;

$val = static fn (string $k, string $d = ''): string => $v->e($old[$k] ?? $d);
$err = static fn (string $k): string => isset($errors[$k]) ? '<p class="field-error" id="err-' . $k . '">' . $v->e($errors[$k]) . '</p>' : '';
?>
<?= $v->partial('partials/page-head', ['kicker' => 'Commande', 'title' => 'Finaliser votre commande', 'lead' => 'Renseignez l\'adresse de livraison et choisissez comment payer. Vous recevrez un e-mail de confirmation avec le récapitulatif.', 'crumbs' => [[$v->url('/panier'), 'Panier'], ['', 'Commande']]]) ?>

<section class="section section--flush" id="formulaire">
    <div class="wrap checkout">
        <form class="checkout__form" method="post" action="<?= $v->url('/commande') ?>" novalidate>
            <?= $v->csrfField() ?>
            <input type="hidden" name="_time" value="<?= time() ?>">
            <div class="hp" aria-hidden="true"><label for="website">Site web</label><input id="website" name="website" type="text" tabindex="-1" autocomplete="off"></div>

            <fieldset class="form-block">
                <legend>Vos coordonnées</legend>
                <div class="form-row">
                    <div class="field<?= isset($errors['first_name']) ? ' is-invalid' : '' ?>">
                        <label for="first_name">Prénom</label>
                        <input id="first_name" name="first_name" type="text" autocomplete="given-name" required maxlength="60" value="<?= $val('first_name') ?>"<?= isset($errors['first_name']) ? ' aria-invalid="true" aria-describedby="err-first_name"' : '' ?>>
                        <?= $err('first_name') ?>
                    </div>
                    <div class="field<?= isset($errors['last_name']) ? ' is-invalid' : '' ?>">
                        <label for="last_name">Nom</label>
                        <input id="last_name" name="last_name" type="text" autocomplete="family-name" required maxlength="60" value="<?= $val('last_name') ?>">
                        <?= $err('last_name') ?>
                    </div>
                </div>
                <div class="form-row">
                    <div class="field<?= isset($errors['email']) ? ' is-invalid' : '' ?>">
                        <label for="email">E-mail</label>
                        <input id="email" name="email" type="email" autocomplete="email" required maxlength="254" value="<?= $val('email') ?>">
                        <?= $err('email') ?>
                    </div>
                    <div class="field">
                        <label for="phone">Téléphone <span class="hint">(facultatif)</span></label>
                        <input id="phone" name="phone" type="tel" autocomplete="tel" maxlength="30" value="<?= $val('phone') ?>" placeholder="+41 79 000 00 00">
                        <?= $err('phone') ?>
                    </div>
                </div>
            </fieldset>

            <fieldset class="form-block">
                <legend>Adresse de livraison</legend>
                <div class="field<?= isset($errors['street']) ? ' is-invalid' : '' ?>">
                    <label for="street">Rue et numéro</label>
                    <input id="street" name="street" type="text" autocomplete="street-address" required maxlength="120" value="<?= $val('street') ?>">
                    <?= $err('street') ?>
                </div>
                <div class="form-row">
                    <div class="field<?= isset($errors['postal_code']) ? ' is-invalid' : '' ?>">
                        <label for="postal_code">NPA</label>
                        <input id="postal_code" name="postal_code" type="text" inputmode="numeric" autocomplete="postal-code" required maxlength="10" value="<?= $val('postal_code') ?>">
                        <?= $err('postal_code') ?>
                    </div>
                    <div class="field<?= isset($errors['city']) ? ' is-invalid' : '' ?>">
                        <label for="city">Localité</label>
                        <input id="city" name="city" type="text" autocomplete="address-level2" required maxlength="80" value="<?= $val('city') ?>">
                        <?= $err('city') ?>
                    </div>
                    <div class="field<?= isset($errors['canton']) ? ' is-invalid' : '' ?>">
                        <label for="canton">Canton</label>
                        <select id="canton" name="canton" required>
                            <option value="">Choisir…</option>
                            <?php foreach ($cantons as $code => $name) : ?>
                            <option value="<?= $v->e($code) ?>" <?= ($old['canton'] ?? '') === $code ? 'selected' : '' ?>><?= $v->e($name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('canton') ?>
                    </div>
                </div>
                <div class="field">
                    <label for="note">Instructions de livraison <span class="hint">(facultatif)</span></label>
                    <textarea id="note" name="note" rows="2" maxlength="500"><?= $val('note') ?></textarea>
                </div>
            </fieldset>

            <fieldset class="form-block" id="livraison">
                <legend>Livraison</legend>
                <?php foreach ($shippingMethods as $key => $method) :
                    $price = (int) $method['price'];
                    $free = $key === 'economy' && $totals->subtotal() >= $freeShippingFrom;
                    ?>
                <label class="choice">
                    <input type="radio" name="shipping" value="<?= $v->e($key) ?>" <?= $selectedShipping === $key ? 'checked' : '' ?> required>
                    <span class="choice__body">
                        <strong><?= $v->e((string) $method['label']) ?></strong>
                        <span><?= $v->e((string) $method['delay']) ?></span>
                    </span>
                    <span class="choice__price"><?= $free ? 'Offerte' : $v->money($price) ?></span>
                </label>
                <?php endforeach; ?>
                <?= $err('shipping') ?>
            </fieldset>

            <fieldset class="form-block" id="paiement">
                <legend>Paiement</legend>
                <?php foreach ($paymentMethods as $key => $method) : ?>
                <label class="choice">
                    <input type="radio" name="payment" value="<?= $v->e($key) ?>" <?= ($old['payment'] ?? 'invoice') === $key ? 'checked' : '' ?> required>
                    <span class="choice__body">
                        <strong><?= $v->e((string) $method['label']) ?></strong>
                        <span><?= $v->e((string) $method['hint']) ?></span>
                    </span>
                </label>
                <?php endforeach; ?>
                <?= $err('payment') ?>
                <p class="hint">Aucune donnée de carte n'est stockée sur ce site. Un prestataire de paiement peut être branché ultérieurement.</p>
            </fieldset>

            <fieldset class="form-block">
                <legend>Options</legend>
                <label class="check">
                    <input type="checkbox" name="gift_wrap" value="1" <?= $giftWrap ? 'checked' : '' ?>>
                    <span>Emballage cadeau avec ruban rose (+ <?= $v->money($giftWrapPrice) ?>)</span>
                </label>
                <div class="field">
                    <label for="gift_message">Message sur la carte <span class="hint">(si emballage cadeau)</span></label>
                    <input id="gift_message" name="gift_message" type="text" maxlength="200" value="<?= $val('gift_message') ?>">
                </div>
                <label class="check<?= isset($errors['terms']) ? ' is-invalid' : '' ?>">
                    <input type="checkbox" name="terms" value="1" required>
                    <span>J'accepte les <a href="<?= $v->url('/cgv') ?>" target="_blank" rel="noopener">conditions générales de vente</a> et la <a href="<?= $v->url('/confidentialite') ?>" target="_blank" rel="noopener">politique de confidentialité</a>.</span>
                </label>
                <?= $err('terms') ?>
            </fieldset>

            <button class="btn btn--primary btn--lg btn--block" type="submit">Confirmer la commande <svg aria-hidden="true"><use href="#i-lock"/></svg></button>
        </form>

        <aside class="summary summary--sticky">
            <h2 class="summary__title">Votre commande</h2>
            <ul class="checkout-items">
                <?php foreach ($items as $item) : ?>
                <li>
                    <?= ProductArt::product($item->product(), 'checkout-items__art') ?>
                    <div>
                        <strong><?= $v->e($item->label()) ?></strong>
                        <span>× <?= $item->quantity() ?></span>
                    </div>
                    <span><?= $v->money($item->total()) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <dl class="summary__rows">
                <div><dt>Sous-total</dt><dd><?= $v->money($totals->subtotal()) ?></dd></div>
                <?php if ($totals->discount() > 0) : ?><div class="is-discount"><dt>Réduction</dt><dd>− <?= $v->money($totals->discount()) ?></dd></div><?php endif; ?>
                <div class="summary__total"><dt>Total estimé</dt><dd><?= $v->money($totals->total()) ?></dd></div>
            </dl>
            <p class="hint">Les frais de port définitifs sont calculés selon le mode choisi.</p>
        </aside>
    </div>
</section>
