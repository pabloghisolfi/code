<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Http\Response;

/**
 * Guide & conseils : routine, sous-ton, taille de montre, entretien, glossaire.
 */
final class GuideController extends AbstractController
{
    public function index(): Response
    {
        $catalog = $this->services->catalog();

        return $this->page('guide', [
            'foundation' => $catalog->bySlug('fond-de-teint-voile-de-soie'),
            'watches' => array_values(array_filter(
                $catalog->byCategory('montres'),
                static fn ($p): bool => !in_array($p->subcategory(), ['accessoires', 'coffrets'], true)
            )),
            'starterKit' => $catalog->bySlugs(['mascara-ciel-ouvert', 'blush-creme-joues-roses', 'baume-levres-teinte-rosee', 'correcteur-eclat']),
        ], [
            'title' => 'Guide & conseils : maquillage, teintes et montres – Azurine',
            'description' => "Trouver sa teinte, se maquiller en cinq minutes, choisir la taille de sa montre et en prendre soin : le guide pratique d'Azurine.",
            'bodyClass' => 'page-conseils',
        ]);
    }
}
