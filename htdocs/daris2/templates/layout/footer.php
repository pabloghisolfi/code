<?php
/**
 * Pied de page : réassurance, colonnes, lettre d'information, mentions.
 *
 * @var \Daris\View\View $v
 * @var array<string, string|int> $app
 * @var string $currentPath
 * @var list<\Daris\Catalog\Category> $navCategories
 */
require __DIR__ . '/../partials/app-fallback.php';

?>
<footer class="footer">
    <div class="wrap">
        <ul class="reassure" aria-label="Nos engagements">
            <li><svg aria-hidden="true"><use href="#i-truck"/></svg><div><strong>Livraison offerte dès CHF 80</strong><span>Depuis Lausanne, 2 à 3 jours ouvrés</span></div></li>
            <li><svg aria-hidden="true"><use href="#i-refresh"/></svg><div><strong>30 jours pour changer d'avis</strong><span>Étiquette de retour gratuite</span></div></li>
            <li><svg aria-hidden="true"><use href="#i-shield"/></svg><div><strong>Montres garanties 2 ans</strong><span>Pile offerte pendant la garantie</span></div></li>
            <li><svg aria-hidden="true"><use href="#i-lock"/></svg><div><strong>Paiement sécurisé</strong><span>Facture, TWINT ou carte</span></div></li>
        </ul>

        <div class="footer__grid">
            <div class="footer__brand">
                <?= $v->partial('partials/logo', ['variant' => 'footer']) ?>
                <p>Du maquillage doux et des montres élégantes, choisis pour les filles et les jeunes femmes qui aiment les jolies choses bien faites. Prix justes en francs suisses, tout compris.</p>
                <ul class="footer__contact">
                    <li><svg aria-hidden="true"><use href="#i-pin"/></svg><?= $v->e((string) $app['address']) ?></li>
                    <li><svg aria-hidden="true"><use href="#i-mail"/></svg><a href="mailto:<?= $v->e((string) $app['email']) ?>"><?= $v->e((string) $app['email']) ?></a></li>
                    <li><svg aria-hidden="true"><use href="#i-phone"/></svg><a href="tel:<?= $v->e(preg_replace('/\s+/', '', (string) $app['phone']) ?? '') ?>"><?= $v->e((string) $app['phone']) ?></a> · lun–ven 9 h–17 h</li>
                </ul>
            </div>

            <nav class="footer__col" aria-label="Boutique">
                <h2 class="footer__title">Boutique</h2>
                <ul>
                    <?php foreach ($navCategories as $category) : ?>
                    <li><a href="<?= $v->url('/boutique/' . $category->slug()) ?>"><?= $v->e($category->name()) ?></a></li>
                    <?php endforeach; ?>
                    <li><a href="<?= $v->url('/boutique', ['badge' => 'nouveau']) ?>">Nouveautés</a></li>
                    <li><a href="<?= $v->url('/boutique', ['badge' => 'best-seller']) ?>">Best-sellers</a></li>
                    <li><a href="<?= $v->url('/boutique', ['badge' => 'promo']) ?>">Offres du moment</a></li>
                    <li><a href="<?= $v->url('/inspirations') ?>">Inspirations</a></li>
                </ul>
            </nav>

            <nav class="footer__col" aria-label="Aide">
                <h2 class="footer__title">Aide</h2>
                <ul>
                    <li><a href="<?= $v->url('/tarifs') ?>">Prix & livraison</a></li>
                    <li><a href="<?= $v->url('/tarifs') ?>#retours">Retours & garantie</a></li>
                    <li><a href="<?= $v->url('/commande/suivi') ?>">Suivre ma commande</a></li>
                    <li><a href="<?= $v->url('/conseillere') ?>">Conseillère & FAQ</a></li>
                    <li><a href="<?= $v->url('/guide') ?>">Guide & conseils</a></li>
                    <li><a href="<?= $v->url('/contact') ?>">Contact</a></li>
                </ul>
            </nav>

            <div class="footer__col footer__newsletter" id="newsletter">
                <h2 class="footer__title">La lettre Daris</h2>
                <p>Nouveautés, conseils de teintes et une attention pour votre anniversaire. Un e-mail par mois, pas plus.</p>
                <form class="newsletter" action="<?= $v->url('/newsletter') ?>" method="post" data-newsletter>
                    <?= $v->csrfField() ?>
                    <input type="hidden" name="back" value="<?= $v->e($currentPath) ?>">
                    <input type="hidden" name="_time" value="<?= time() ?>">
                    <div class="hp" aria-hidden="true"><label for="nl-website">Site web</label><input id="nl-website" name="website" type="text" tabindex="-1" autocomplete="off"></div>
                    <label class="sr-only" for="nl-email">Votre e-mail</label>
                    <input class="newsletter__input" id="nl-email" type="email" name="email" placeholder="votre@email.ch" required maxlength="254" autocomplete="email">
                    <button class="btn btn--primary" type="submit">Je m'inscris</button>
                </form>
                <p class="newsletter__status" data-newsletter-status role="status"></p>
            </div>
        </div>

        <div class="footer__bottom">
            <ul class="footer__payments" aria-label="Moyens de paiement">
                <li><svg aria-hidden="true"><use href="#i-invoice"/></svg>Facture</li>
                <li><svg aria-hidden="true"><use href="#i-twint"/></svg>TWINT</li>
                <li><svg aria-hidden="true"><use href="#i-card"/></svg>Carte</li>
                <li><svg aria-hidden="true"><use href="#i-swiss"/></svg>Livraison en Suisse</li>
            </ul>
            <ul class="footer__legal">
                <li>© <?= date('Y') ?> <?= $v->e((string) $app['name']) ?></li>
                <li><a href="<?= $v->url('/cgv') ?>">Conditions générales</a></li>
                <li><a href="<?= $v->url('/confidentialite') ?>">Confidentialité</a></li>
                <li><a href="<?= $v->url('/sitemap.xml') ?>">Plan du site</a></li>
            </ul>
        </div>
        <p class="footer__credit"><?= $v->e((string) $app['credit']) ?></p>
    </div>
</footer>
