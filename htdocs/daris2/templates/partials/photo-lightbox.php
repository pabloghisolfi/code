<?php
/**
 * Visionneuse photo (reste sur Azurine : fermeture + liens retour).
 *
 * @var \Daris\View\View $v
 */
?>
<div class="lightbox" id="photo-lightbox" data-photo-lightbox-root hidden>
    <div class="lightbox__backdrop" data-photo-lightbox-close tabindex="-1" aria-hidden="true"></div>
    <div class="lightbox__dialog" role="dialog" aria-modal="true" aria-labelledby="photo-lightbox-title" data-photo-lightbox-dialog>
        <div class="lightbox__toolbar">
            <button type="button" class="lightbox__close" data-photo-lightbox-close>
                <svg aria-hidden="true"><use href="#i-close"/></svg>
                <span><?= $v->e($v->t('photo.close', 'Fermer')) ?></span>
            </button>
        </div>
        <h2 class="sr-only" id="photo-lightbox-title"><?= $v->e($v->t('photo.view_title', 'Photo produit')) ?></h2>
        <figure class="lightbox__figure">
            <img class="lightbox__img" alt="" data-photo-lightbox-img hidden decoding="async" draggable="false">
            <div class="lightbox__svg" data-photo-lightbox-svg hidden></div>
        </figure>
        <nav class="lightbox__nav" aria-label="<?= $v->e($v->t('photo.nav', 'Navigation photo')) ?>">
            <a class="btn btn--soft btn--sm" href="#" data-photo-lightbox-product hidden><?= $v->e($v->t('photo.back_product', 'Retour au produit')) ?></a>
            <a class="btn btn--ghost btn--sm" href="<?= $v->url('/boutique') ?>" data-photo-lightbox-shop><?= $v->e($v->t('photo.back_shop', 'Boutique')) ?></a>
        </nav>
    </div>
</div>
