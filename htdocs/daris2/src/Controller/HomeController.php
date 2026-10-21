<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Http\Response;

/**
 * Page d'accueil.
 */
final class HomeController extends AbstractController
{
    public function index(): Response
    {
        $catalog = $this->services->catalog();

        return $this->page('home', [
            'categories' => $catalog->categories(),
            'featured' => $catalog->featured(8),
            'newProducts' => $catalog->withBadge('nouveau', 4),
            'looks' => array_slice($this->services->looks()->all(), 0, 3),
            'faq' => $this->services->faq()->highlights(4),
            'priceBounds' => [
                'maquillage' => $catalog->priceBounds('maquillage'),
                'montres' => $catalog->priceBounds('montres'),
            ],
            'watchBrands' => array_slice($catalog->brands('montres'), 0, 10),
            'makeupBrands' => array_slice($catalog->brands('maquillage'), 0, 10),
            'productCount' => count($catalog->all()),
        ], [
            'title' => 'Azurine – Haute beauté & horlogerie multimarque',
            'description' => 'Boutique suisse Azurine : grandes maisons de maquillage et montres d\'exception, prix réalistes en CHF. Conseillère intégrée, livraison en Suisse.',
            'ogType' => 'website',
            'bodyClass' => 'page-home',
            'canonical' => $this->services->url()->absolute('/'),
        ]);
    }
}
