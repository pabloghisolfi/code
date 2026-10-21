<?php
/**
 * Crédit photo Wikimedia / licence libre.
 *
 * @var \Daris\View\View $v
 * @var \Daris\Catalog\Product $product
 */
if (!$product->hasPhoto()) {
    return;
}
$photo = $product->photo();
?>
<p class="photo-credit">
    Photo : <?= $v->e((string) $photo['author']) ?> —
    <a href="<?= $v->e((string) $photo['source']) ?>" rel="noopener noreferrer" target="_blank">Wikimedia Commons</a> —
    <?= $v->e((string) $photo['license']) ?>.
    Visuel illustratif ; le modèle Azurine peut différer. Utilisez « Voir la photo » pour agrandir l’image hébergée ici — pas de redirection vers une boutique de marque.
</p>
