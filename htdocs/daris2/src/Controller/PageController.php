<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Http\Response;

/**
 * Pages éditoriales : à propos, confidentialité, conditions générales.
 */
final class PageController extends AbstractController
{
    public function about(): Response
    {
        return $this->page('about', [
            'categories' => $this->services->catalog()->categories(),
            'bestSellers' => $this->services->catalog()->withBadge('best-seller', 4),
        ], [
            'title' => "À propos d'Azurine – maquillage & montres",
            'description' => 'Azurine est une boutique en ligne suisse de maquillage doux et de montres élégantes : formules soignées, mouvements fiables, prix justes en CHF.',
            'bodyClass' => 'page-about',
        ]);
    }

    public function privacy(): Response
    {
        return $this->page('privacy', [
            'sessionName' => $this->services->config()->string('security.session_name'),
            'sessionHours' => intdiv($this->services->config()->int('security.session_lifetime'), 3600),
        ], [
            'title' => 'Confidentialité – Azurine',
            'description' => 'Quelles données la boutique Azurine conserve (commandes, messages, panier), pourquoi, combien de temps, et comment les faire effacer.',
            'bodyClass' => 'page-legal',
        ]);
    }

    public function terms(): Response
    {
        return $this->page('terms', [
            'freeShippingFrom' => $this->services->config()->int('shop.free_shipping_from'),
            'shippingMethods' => $this->services->config()->array('shop.shipping_methods'),
        ], [
            'title' => 'Conditions générales de vente – Azurine',
            'description' => 'Conditions de vente de la boutique Azurine : prix, paiement, livraison, retours sous 30 jours, garantie des montres.',
            'bodyClass' => 'page-legal',
        ]);
    }
}
