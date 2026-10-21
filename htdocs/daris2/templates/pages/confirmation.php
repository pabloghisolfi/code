<?php
/**
 * @var \Daris\View\View $v
 * @var \Daris\Order\Order $order
 * @var string $email
 * @var array<string, mixed> $paymentMethods
 * @var bool $justPlaced
 */
$payLabel = is_array($paymentMethods[$order->paymentMethod()] ?? null) ? (string) ($paymentMethods[$order->paymentMethod()]['label'] ?? $order->paymentMethod()) : $order->paymentMethod();
$totals = $order->totals();
?>
<?= $v->partial('partials/page-head', ['kicker' => 'Merci', 'title' => 'Commande ' . $order->reference() . ' confirmée', 'lead' => 'Un e-mail de confirmation part vers ' . $order->customer()->email() . '. Conservez cette page : elle contient le récapitulatif complet.', 'crumbs' => [['', 'Confirmation']], 'tone' => 'pink']) ?>

<section class="section section--flush">
    <div class="wrap">
        <div class="order-confirm" data-reveal>
            <div class="order-confirm__banner">
                <svg aria-hidden="true"><use href="#i-check"/></svg>
                <p><?= $justPlaced ? 'Votre commande est enregistrée.' : 'Récapitulatif de commande.' ?></p>
            </div>

            <div class="order-grid">
                <div class="order-panel">
                    <h2 class="h3">Articles</h2>
                    <table class="order-table">
                        <thead><tr><th>Produit</th><th>Qté</th><th class="num">Prix</th></tr></thead>
                        <tbody>
                            <?php foreach ($order->items() as $line) : ?>
                            <tr>
                                <td><?= $v->e($line['name']) ?><?= ($line['variant'] ?? '') !== '' && $line['variant'] !== null ? ' · ' . $v->e((string) $line['variant']) : '' ?></td>
                                <td><?= (int) $line['quantity'] ?></td>
                                <td class="num"><?= $v->money((int) $line['total']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <dl class="summary__rows">
                        <div><dt>Sous-total</dt><dd><?= $v->money((int) ($totals['subtotal'] ?? 0)) ?></dd></div>
                        <?php if ((int) ($totals['discount'] ?? 0) > 0) : ?><div><dt>Réduction</dt><dd>− <?= $v->money((int) $totals['discount']) ?></dd></div><?php endif; ?>
                        <div><dt><?= $v->e((string) ($totals['shipping_label'] ?? 'Livraison')) ?></dt><dd><?= (int) ($totals['shipping'] ?? 0) === 0 ? 'Offerte' : $v->money((int) $totals['shipping']) ?></dd></div>
                        <?php if ((int) ($totals['gift_wrap'] ?? 0) > 0) : ?><div><dt>Emballage cadeau</dt><dd><?= $v->money((int) $totals['gift_wrap']) ?></dd></div><?php endif; ?>
                        <div class="summary__total"><dt>Total</dt><dd><?= $v->money($order->total()) ?></dd></div>
                    </dl>
                </div>
                <div class="order-panel">
                    <h2 class="h3">Livraison</h2>
                    <p><strong><?= $v->e($order->customer()->fullName()) ?></strong><br>
                    <?= $v->e($order->customer()->street()) ?><br>
                    <?= $v->e($order->customer()->postalCode()) ?> <?= $v->e($order->customer()->city()) ?><br>
                    <?= $v->e($order->customer()->cantonName()) ?></p>
                    <?php if ($order->customer()->phone() !== '') : ?><p>Tél. <?= $v->e($order->customer()->phone()) ?></p><?php endif; ?>
                    <h2 class="h3">Paiement</h2>
                    <p><?= $v->e($payLabel) ?><?php if ($order->paymentReference() !== null) : ?><br><span class="hint">Réf. <?= $v->e($order->paymentReference()) ?></span><?php endif; ?></p>
                    <p class="hint"><?= $v->e((string) ($totals['payment_label'] ?? '')) ?></p>
                </div>
            </div>

            <details class="email-preview">
                <summary>Voir le texte envoyé par e-mail</summary>
                <pre><?= $v->e($email) ?></pre>
            </details>

            <div class="actions">
                <a class="btn btn--primary" href="<?= $v->url('/boutique') ?>">Continuer mes achats</a>
                <a class="btn btn--soft" href="<?= $v->url('/contact') ?>">Une question ?</a>
            </div>
        </div>
    </div>
</section>
