<?php
/**
 * @var \Daris\View\View $v
 * @var array<string, string> $subjects
 * @var array<string, string> $old
 * @var array<string, string> $errors
 * @var bool $sent
 * @var array<string, string|int> $app
 */
$val = static fn (string $k, string $d = ''): string => $v->e($old[$k] ?? $d);
?>
<?= $v->partial('partials/page-head', ['kicker' => 'Contact', 'title' => 'Écrire à l\'équipe Daris', 'lead' => 'Une question sur une commande, une teinte ou une montre ? Nous répondons en général sous 24 heures ouvrées.', 'crumbs' => [['', 'Contact']]]) ?>

<section class="section">
    <div class="wrap contact-layout">
        <?php if ($sent) : ?>
        <div class="form-success" role="status" data-reveal>
            <svg aria-hidden="true"><use href="#i-check"/></svg>
            <h2 class="h3">Message envoyé</h2>
            <p>Merci. Nous avons bien reçu votre message et vous répondrons à l'adresse indiquée.</p>
        </div>
        <?php endif; ?>

        <form class="form-card" id="formulaire" method="post" action="<?= $v->url('/contact') ?>" novalidate>
            <?= $v->csrfField() ?>
            <input type="hidden" name="_time" value="<?= time() ?>">
            <div class="hp" aria-hidden="true"><label for="ct-website">Site</label><input id="ct-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>

            <div class="field<?= isset($errors['name']) ? ' is-invalid' : '' ?>">
                <label for="name">Votre nom</label>
                <input id="name" name="name" type="text" required maxlength="80" autocomplete="name" value="<?= $val('name') ?>">
                <?php if (isset($errors['name'])) : ?><p class="field-error"><?= $v->e($errors['name']) ?></p><?php endif; ?>
            </div>
            <div class="field<?= isset($errors['email']) ? ' is-invalid' : '' ?>">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" required maxlength="254" autocomplete="email" value="<?= $val('email') ?>">
                <?php if (isset($errors['email'])) : ?><p class="field-error"><?= $v->e($errors['email']) ?></p><?php endif; ?>
            </div>
            <div class="field<?= isset($errors['subject']) ? ' is-invalid' : '' ?>">
                <label for="subject">Sujet</label>
                <select id="subject" name="subject" required>
                    <?php foreach ($subjects as $key => $label) : ?>
                    <option value="<?= $v->e($key) ?>" <?= ($old['subject'] ?? 'autre') === $key ? 'selected' : '' ?>><?= $v->e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label for="order_reference">Référence commande <span class="hint">(facultatif)</span></label>
                <input id="order_reference" name="order_reference" type="text" maxlength="20" placeholder="DA-2026-ABC123" value="<?= $val('order_reference') ?>">
                <?php if (isset($errors['order_reference'])) : ?><p class="field-error"><?= $v->e($errors['order_reference']) ?></p><?php endif; ?>
            </div>
            <div class="field<?= isset($errors['message']) ? ' is-invalid' : '' ?>">
                <label for="message">Message</label>
                <textarea id="message" name="message" rows="6" required minlength="10" maxlength="4000"><?= $val('message') ?></textarea>
                <?php if (isset($errors['message'])) : ?><p class="field-error"><?= $v->e($errors['message']) ?></p><?php endif; ?>
            </div>
            <button class="btn btn--primary btn--block" type="submit">Envoyer <svg aria-hidden="true"><use href="#i-send"/></svg></button>
            <p class="hint">Vos données sont traitées conformément à la page <a href="<?= $v->url('/confidentialite') ?>">Confidentialité</a>.</p>
        </form>

        <aside class="contact-aside" data-reveal>
            <div class="contact-card">
                <h2 class="h3">Coordonnées</h2>
                <p><?= $v->e((string) $app['address']) ?></p>
                <p><a href="mailto:<?= $v->e((string) $app['email']) ?>"><?= $v->e((string) $app['email']) ?></a></p>
                <p><a href="tel:<?= $v->e(preg_replace('/\s+/', '', (string) $app['phone']) ?? '') ?>"><?= $v->e((string) $app['phone']) ?></a></p>
            </div>
            <div class="contact-card">
                <h2 class="h3">Réponse immédiate</h2>
                <p>Teinte, taille de montre, idée cadeau : la conseillère Léa répond tout de suite.</p>
                <a class="btn btn--soft btn--block" href="<?= $v->url('/conseillere') ?>">Ouvrir la conseillère</a>
            </div>
        </aside>
    </div>
</section>
