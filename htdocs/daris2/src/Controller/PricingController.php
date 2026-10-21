<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Catalog\Product;
use Daris\Http\Response;

/**
 * Page « Prix & livraison » : grille tarifaire complète en CHF, frais de
 * port, services atelier, retours et questions fréquentes.
 */
final class PricingController extends AbstractController
{
    private const SERVICES = [
        ['name' => 'Changement de pile', 'price' => 2000, 'note' => 'Offert pendant la garantie, test d\'étanchéité inclus.'],
        ['name' => 'Mise à taille d\'un bracelet acier', 'price' => 0, 'note' => 'Gratuit à la commande (indiquez votre tour de poignet) ou en atelier.'],
        ['name' => 'Changement de bracelet (pose)', 'price' => 1000, 'note' => 'Offert si le bracelet est acheté chez Azurine.'],
        ['name' => 'Gravure au dos du boîtier', 'price' => 2500, 'note' => 'Jusqu\'à 30 caractères, délai +2 jours ouvrés.'],
        ['name' => 'Papier cadeau et carte manuscrite', 'price' => 490, 'note' => 'Boîte bleu ciel, ruban rose, message de votre choix.'],
        ['name' => 'Révision complète (hors garantie)', 'price' => 6500, 'note' => 'Nettoyage, pile, joints, test d\'étanchéité.'],
    ];

    private const MARKET = [
        ['item' => 'Rouge à lèvres grande maison', 'market' => 'CHF 45 à 58', 'azurine' => 5200],
        ['item' => 'Fond de teint prestige 30 ml', 'market' => 'CHF 65 à 98', 'azurine' => 7800],
        ['item' => 'Montre sport acier 40 mm', 'market' => 'CHF 6 500 à 14 000', 'azurine' => 785000],
        ['item' => 'Montre iconique acier', 'market' => 'CHF 9 000 à 18 000', 'azurine' => 1285000],
        ['item' => 'Horlogerie haute gamme', 'market' => 'CHF 35 000 à 120 000', 'azurine' => 8950000],
        ['item' => 'Royal Oak / Nautilus (marché)', 'market' => 'CHF 28 000 à 95 000', 'azurine' => 4280000],
    ];

    public function index(): Response
    {
        $catalog = $this->services->catalog();
        $config = $this->services->config();

        $byCategory = [];
        foreach ($catalog->categories() as $category) {
            $products = $catalog->byCategory($category->slug());
            usort($products, static fn (Product $a, Product $b): int => $a->price() <=> $b->price());
            $byCategory[] = ['category' => $category, 'products' => $products];
        }

        return $this->page('pricing', [
            'byCategory' => $byCategory,
            'shippingMethods' => $config->array('shop.shipping_methods'),
            'freeShippingFrom' => $config->int('shop.free_shipping_from'),
            'giftWrapPrice' => $config->int('shop.gift_wrap_price'),
            'vatRate' => $config->float('shop.vat_rate'),
            'services' => self::SERVICES,
            'market' => self::MARKET,
            'promoCodes' => $config->array('shop.promo_codes'),
            'faq' => $this->services->faq()->topics(),
        ], [
            'title' => 'Prix, livraison et services – Azurine',
            'description' => 'Grille Azurine en CHF, TVA incluse : maquillage des grandes maisons dès CHF 45, montres d\'exception dès CHF 3 850, livraison assurée, retours 30 jours.',
            'bodyClass' => 'page-pricing',
        ]);
    }
}
