<?php
/**
 * @var \Daris\View\View $v
 * @var \Daris\Contact\ContactMessage $message
 * @var string $shopEmail
 */
?>
Bonjour <?= $v->e($message->name()) ?>,

Nous avons bien reçu votre message concernant « <?= $v->e($message->subjectLabel()) ?> ».

Nous vous répondons en général sous 24 heures ouvrées à l'adresse <?= $v->e($message->email()) ?>.

Pour une réponse immédiate sur les teintes ou les tailles de montres, vous pouvez aussi utiliser la conseillère en ligne sur le site.

— L'équipe Daris
<?= $shopEmail ?>

