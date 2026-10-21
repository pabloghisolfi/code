<?php
/**
 * En-tête de page intérieure avec fil d'Ariane.
 *
 * @var \Daris\View\View $v
 * @var string $kicker
 * @var string $title
 * @var string $lead
 * @var list<array{0: string, 1: string}> $crumbs  [[href, label], …]
 * @var string $tone  "blue" | "pink"
 */
$crumbs = $crumbs ?? [];
$tone = $tone ?? 'blue';
?>
<section class="<?= $v->classes(['page-head' => true, 'page-head--pink' => $tone === 'pink']) ?>">
    <div class="wrap">
        <?php if ($crumbs !== []) : ?>
        <nav class="crumbs" aria-label="Fil d'Ariane">
            <ol>
                <li><a href="<?= $v->url('/') ?>">Accueil</a></li>
                <?php foreach ($crumbs as [$href, $label]) : ?>
                <li><?php if ($href !== '') : ?><a href="<?= $v->e($href) ?>"><?= $v->e($label) ?></a><?php else : ?><span aria-current="page"><?= $v->e($label) ?></span><?php endif; ?></li>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>
        <?php if (($kicker ?? '') !== '') : ?><p class="kicker"><?= $v->e($kicker) ?></p><?php endif; ?>
        <h1 class="h1"><?= $v->e($title) ?></h1>
        <?php if (($lead ?? '') !== '') : ?><p class="lead"><?= $v->e($lead) ?></p><?php endif; ?>
    </div>
</section>
