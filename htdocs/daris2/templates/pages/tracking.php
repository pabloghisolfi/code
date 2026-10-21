<?php
/**
 * @var \Daris\View\View $v
 * @var \Daris\Order\Order|null $order
 * @var array<string, string> $old
 * @var array<string, string> $errors
 */
$val = static fn (string $k): string => $v->e($old[$k] ?? '');
?>
<?= $v->partial('partials/page-head', ['kicker' => 'Suivi', 'title' => 'Suivre ma commande', 'lead' => 'Entrez la référence reçue par e-mail (DA-2026-XXXXXX) et l\'adresse utilisée lors de la commande.', 'crumbs' => [['', 'Suivi']]]) ?>

<section class="section">
    <div class="wrap narrow">
        <form class="form-card" method="post" action="<?= $v->url('/commande/suivi') ?>">
            <?= $v->csrfField() ?>
            <input type="hidden" name="_time" value="<?= time() ?>">
            <div class="hp" aria-hidden="true"><label for="tr-website">Site</label><input id="tr-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
            <div class="field<?= isset($errors['reference']) ? ' is-invalid' : '' ?>">
                <label for="reference">Référence de commande</label>
                <input id="reference" name="reference" type="text" required maxlength="20" placeholder="DA-2026-ABC123" value="<?= $val('reference') ?>">
                <?php if (isset($errors['reference'])) : ?><p class="field-error"><?= $v->e($errors['reference']) ?></p><?php endif; ?>
            </div>
            <div class="field<?= isset($errors['email']) ? ' is-invalid' : '' ?>">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" required maxlength="254" autocomplete="email" value="<?= $val('email') ?>">
                <?php if (isset($errors['email'])) : ?><p class="field-error"><?= $v->e($errors['email']) ?></p><?php endif; ?>
            </div>
            <button class="btn btn--primary btn--block" type="submit">Voir le statut</button>
        </form>

        <?php if ($order !== null) : ?>
        <div class="track-result" data-reveal>
            <p class="kicker">Commande <?= $v->e($order->reference()) ?></p>
            <p class="track-status"><strong><?= $v->e($order->statusLabel()) ?></strong> · passée le <?= $v->date($order->createdAt()) ?></p>
            <p><?= $order->itemCount() ?> article<?= $order->itemCount() > 1 ? 's' : '' ?> · Total <?= $v->money($order->total()) ?></p>
            <ul class="track-steps">
                <li class="is-done">Confirmée</li>
                <li class="<?= in_array($order->status(), ['preparation', 'expediee', 'livree'], true) ? 'is-done' : '' ?>">En préparation</li>
                <li class="<?= in_array($order->status(), ['expediee', 'livree'], true) ? 'is-done' : '' ?>">Expédiée</li>
                <li class="<?= $order->status() === 'livree' ? 'is-done' : '' ?>">Livrée</li>
            </ul>
            <p class="hint">Une question ? <a href="<?= $v->url('/contact', ['sujet' => 'commande', 'commande' => $order->reference()]) ?>">Écrivez-nous</a> avec votre référence.</p>
        </div>
        <?php endif; ?>
    </div>
</section>
