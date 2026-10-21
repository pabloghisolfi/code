<?php
/**
 * @var \Daris\View\View $v
 * @var string $sessionName
 * @var int $sessionHours
 * @var array<string, string|int> $app
 */
?>
<?= $v->partial('partials/page-head', ['kicker' => 'Confidentialité', 'title' => 'Protection des données', 'lead' => 'Ce que la boutique Azurine enregistre, pourquoi, et comment exercer vos droits.', 'crumbs' => [['', 'Confidentialité']]]) ?>

<section class="section">
    <div class="wrap prose wide legal" data-reveal>
        <h2>Responsable</h2>
        <p>La boutique Azurine, <?= $v->e((string) $app['address']) ?>. Contact : <a href="mailto:<?= $v->e((string) $app['email']) ?>"><?= $v->e((string) $app['email']) ?></a>.</p>

        <h2>Commandes</h2>
        <p>Lors d'une commande, nous enregistrons nom, adresse, e-mail, téléphone (facultatif), articles commandés et montants. Ces données servent à préparer, livrer et facturer la commande, et à répondre au suivi. Conservation : durée légale de facturation, puis archivage limité.</p>

        <h2>Messages de contact</h2>
        <p>Le formulaire de contact enregistre votre nom, e-mail, sujet et message, ainsi qu'un hachage de l'adresse IP (pas l'IP en clair). Conservation le temps de traiter la demande.</p>

        <h2>Panier et session</h2>
        <p>Le panier est stocké dans un cookie de session (<code><?= $v->e($sessionName) ?></code>), durée environ <?= (int) $sessionHours ?> heures, HttpOnly et SameSite. Aucune carte bancaire n'est stockée sur ce site.</p>

        <h2>Lettre d'information</h2>
        <p>Si vous vous inscrivez, seule votre adresse e-mail est conservée jusqu'à désinscription.</p>

        <h2>Conseillère en ligne</h2>
        <p>Les questions posées à la conseillère sont traitées dans le navigateur et sur le serveur pour générer une réponse. Elles ne sont pas associées à votre nom ni conservées dans une base marketing.</p>

        <h2>Limitation de débit</h2>
        <p>Des compteurs anonymes (fichiers temporaires) limitent les abus sur les formulaires.</p>

        <h2>Vos droits</h2>
        <p>Vous pouvez demander l'accès, la rectification ou l'effacement de vos données en écrivant à l'adresse ci-dessus, en indiquant l'e-mail utilisé lors de la commande ou du contact.</p>

        <h2>Sécurité</h2>
        <p>Connexion chiffrée recommandée (HTTPS). En-têtes de sécurité (CSP, X-Frame-Options, etc.), jetons CSRF sur les formulaires, requêtes préparées pour la base de commandes.</p>
    </div>
</section>
