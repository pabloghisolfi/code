<?php
/**
 * Tête de section : surtitre, titre, texte, lien optionnel.
 *
 * @var \Daris\View\View $v
 * @var string $kicker
 * @var string $title
 * @var string $text
 * @var string $linkHref
 * @var string $linkLabel
 * @var string $id
 * @var bool $center
 */
$id = $id ?? '';
$text = $text ?? '';
$linkHref = $linkHref ?? '';
$center = $center ?? false;
?>
<div class="<?= $v->classes(['section-head' => true, 'section-head--center' => $center]) ?>" data-reveal>
    <div>
        <?php if (($kicker ?? '') !== '') : ?><p class="kicker"><?= $v->e($kicker) ?></p><?php endif; ?>
        <h2 class="h2"<?= $id !== '' ? ' id="' . $v->e($id) . '"' : '' ?>><?= $v->e($title) ?></h2>
        <?php if ($text !== '') : ?><p class="lead"><?= $v->e($text) ?></p><?php endif; ?>
    </div>
    <?php if ($linkHref !== '') : ?>
    <a class="link" href="<?= $v->e($linkHref) ?>"><?= $v->e($linkLabel ?? 'Voir tout') ?> <svg aria-hidden="true"><use href="#i-arrow"/></svg></a>
    <?php endif; ?>
</div>
