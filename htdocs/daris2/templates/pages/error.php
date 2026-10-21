<?php
/**
 * @var \Daris\View\View $v
 * @var int $status
 * @var string $heading
 * @var string $lead
 * @var string $debug
 * @var list<\Daris\Catalog\Product> $suggestions
 */
?>
<section class="section">
    <div class="wrap narrow center">
        <p class="error-code" aria-hidden="true"><?= $status ?></p>
        <h1 class="h1"><?= $v->e($heading) ?></h1>
        <p class="lead"><?= $v->e($lead) ?></p>
        <div class="actions">
            <a class="btn btn--primary" href="<?= $v->url('/') ?>">Retour à l'accueil</a>
            <a class="btn btn--soft" href="<?= $v->url('/boutique') ?>">Voir la boutique</a>
        </div>
        <?php if ($debug !== '') : ?>
        <pre class="debug"><?= $v->e($debug) ?></pre>
        <?php endif; ?>
    </div>
</section>
<?php if ($suggestions !== []) : ?>
<section class="section section--tint">
    <div class="wrap">
        <?= $v->partial('partials/section-head', ['kicker' => 'En attendant', 'title' => 'Quelques coups de cœur']) ?>
        <div class="grid grid--products">
            <?php foreach ($suggestions as $product) : ?>
            <?= $v->partial('partials/product-card', ['product' => $product, 'compact' => true]) ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>
