<?php
/**
 * @var \Daris\View\View $v
 * @var \Daris\Contact\ContactMessage $message
 */
?>
Nouveau message — Daris

De : <?= $v->e($message->name()) ?> <<?= $v->e($message->email()) ?>>

Sujet : <?= $v->e($message->subjectLabel()) ?>

<?php if ($message->orderReference() !== '') : ?>
Commande : <?= $v->e($message->orderReference()) ?>

<?php endif; ?>
---
<?= $v->e($message->message()) ?>

