<?php
/**
 * @var \Daris\View\View $v
 * @var int $freeShippingFrom
 * @var array<string, mixed> $shippingMethods
 * @var array<string, string|int> $app
 */
?>
<?= $v->partial('partials/page-head', ['kicker' => 'CGV', 'title' => 'Conditions générales de vente', 'lead' => 'Vente en ligne depuis la Suisse, livraison en Suisse et au Liechtenstein.', 'crumbs' => [['', 'CGV']]]) ?>

<section class="section">
    <div class="wrap prose wide legal" data-reveal>
        <h2>1. Objet</h2>
        <p>Les présentes conditions régissent les ventes de la boutique Daris (maquillage et montres) via le site. Prix en CHF, TVA incluse.</p>

        <h2>2. Commande</h2>
        <p>La commande est confirmée par e-mail avec une référence unique. Les stocks sont indicatifs ; en cas d'indisponibilité, nous vous contactons pour un remboursement ou un échange.</p>

        <h2>3. Paiement</h2>
        <p>Facture à 30 jours (QR-facture), TWINT ou carte selon les moyens proposés au moment de la commande. Le paiement par carte peut être traité par un prestataire externe lorsque celui-ci est branché.</p>

        <h2>4. Livraison</h2>
        <p>Expédition depuis Lausanne. Livraison Economy offerte dès <?= $v->money($freeShippingFrom) ?>. Délais indicatifs selon le mode choisi. Risque de perte transféré à la réception du colis.</p>

        <h2>5. Retours</h2>
        <p>30 jours pour retourner un article non utilisé (maquillage scellé, montre non portée avec écrin). Remboursement sous 5 jours ouvrés après contrôle. Échange de teinte de fond de teint gratuit une fois par commande.</p>

        <h2>6. Garantie montres</h2>
        <p>Deux ans (trois ans pour Minuit Swiss Made) sur le mouvement et l'étanchéité, hors usure normale et chocs.</p>

        <h2>7. Données personnelles</h2>
        <p>Voir la page <a href="<?= $v->url('/confidentialite') ?>">Confidentialité</a>.</p>

        <h2>8. Litiges</h2>
        <p>Droit suisse. For juridique : canton de Vaud, sous réserve des dispositions impératives de protection des consommateurs.</p>

        <p class="hint">Questions : <a href="mailto:<?= $v->e((string) $app['email']) ?>"><?= $v->e((string) $app['email']) ?></a></p>
    </div>
</section>
