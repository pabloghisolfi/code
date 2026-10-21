<?php
/**
 * Carte d'inspiration (look).
 *
 * @var \Daris\View\View $v
 * @var \Daris\Catalog\Look $look
 */
use Daris\View\ProductArt;

$palette = $look->palette();
$main = $palette[0] ?? '#f2b9cc';
?>
<a class="look-card" href="<?= $v->url('/exemples/' . $look->slug()) ?>" data-reveal>
    <div class="look-card__media">
        <?= ProductArt::render($look->art(), $main, count($palette) % 2 === 0 ? 'blue' : 'pink', 'look-card__art') ?>
        <span class="look-card__palette" aria-hidden="true">
            <?php foreach ($palette as $color) : ?><svg viewBox="0 0 10 10"><circle cx="5" cy="5" r="5" fill="<?= $v->e($color) ?>"/></svg><?php endforeach; ?>
        </span>
    </div>
    <div class="look-card__body">
        <p class="kicker"><?= $v->e($look->occasion()) ?> · <?= $v->e($look->duration()) ?></p>
        <h3><?= $v->e($look->title()) ?></h3>
        <p><?= $v->e($look->excerpt()) ?></p>
        <span class="link">Voir le look <svg aria-hidden="true"><use href="#i-arrow"/></svg></span>
    </div>
</a>
