<?php
/**
 * @var \Daris\View\View $v
 * @var \Daris\Order\Order $order
 * @var string $orderUrl
 * @var string $shopEmail
 * @var array<string, mixed> $paymentMethods
 */
$totals = $order->totals();
$pay = is_array($paymentMethods[$order->paymentMethod()] ?? null) ? (string) ($paymentMethods[$order->paymentMethod()]['label'] ?? '') : $order->paymentMethod();
$c = $order->customer();
?>
Bonjour <?= $v->e($c->firstName()) ?>,

Merci pour votre commande chez Daris.

Référence : <?= $order->reference() ?>

<?php foreach ($order->items() as $line) : ?>
- <?= $v->e($line['name']) ?><?= ($line['variant'] ?? '') ? ' · ' . $v->e((string) $line['variant']) : '' ?> × <?= (int) $line['quantity'] ?> — <?= $v->money((int) $line['total']) ?>

<?php endforeach; ?>

Sous-total : <?= $v->money((int) ($totals['subtotal'] ?? 0)) ?>
<?php if ((int) ($totals['discount'] ?? 0) > 0) : ?>Réduction : − <?= $v->money((int) $totals['discount']) ?>

<?php endif; ?>
Livraison : <?= (int) ($totals['shipping'] ?? 0) === 0 ? 'Offerte' : $v->money((int) $totals['shipping']) ?>

Total : <?= $v->money($order->total()) ?>

Livraison à :
<?= $v->e($c->fullName()) ?>

<?= $v->e($c->street()) ?>

<?= $v->e($c->postalCode()) ?> <?= $v->e($c->city()) ?>

Paiement : <?= $v->e($pay) ?>

Voir le détail : <?= $orderUrl ?>


Une question ? <?= $shopEmail ?>


— Daris
