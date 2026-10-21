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
        ], [
            'title' => 'Daris – Maquillage & montres pour elles, livrés en Suisse',
            'description' => 'Boutique en ligne suisse de maquillage doux et de montres élégantes. Teintes pastel, prix en CHF, livraison offerte dès CHF 80, retours gratuits 30 jours.',
            'ogType' => 'website',
            'bodyClass' => 'page-home',
            'canonical' => $this->services->url()->absolute('/'),
        ]);
    }
}
