<?php
/**
 * Visionneuse photo plein écran (fichiers hébergés sur Azurine, pas de lien boutique).
 *
 * @var \Daris\View\View $v
 */
?>
<div class="lightbox" id="photo-lightbox" data-photo-lightbox-root hidden>
    <div class="lightbox__backdrop" data-photo-lightbox-close tabindex="-1" aria-hidden="true"></div>
    <div class="lightbox__dialog" role="dialog" aria-modal="true" aria-labelledby="photo-lightbox-title">
        <h2 class="sr-only" id="photo-lightbox-title">Photo produit</h2>
        <button type="button" class="lightbox__close" data-photo-lightbox-close aria-label="Fermer">
            <svg aria-hidden="true"><use href="#i-close"/></svg>
        </button>
        <figure class="lightbox__figure">
            <img class="lightbox__img" alt="" data-photo-lightbox-img hidden decoding="async">
            <div class="lightbox__svg" data-photo-lightbox-svg hidden></div>
        </figure>
    </div>
</div>
