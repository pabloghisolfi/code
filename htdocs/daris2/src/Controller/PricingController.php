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
        ['item' => 'Rouge à lèvres satiné', 'market' => 'CHF 25 à 48', 'azurine' => 2900],
        ['item' => 'Mascara volume', 'market' => 'CHF 22 à 42', 'azurine' => 2600],
        ['item' => 'Palette 9 fards', 'market' => 'CHF 45 à 85', 'azurine' => 4900],
        ['item' => 'Fond de teint 30 ml avec SPF', 'market' => 'CHF 39 à 72', 'azurine' => 4200],
        ['item' => 'Montre quartz acier, 32 mm', 'market' => 'CHF 179 à 350', 'azurine' => 18900],
        ['item' => 'Montre Swiss Made, saphir', 'market' => 'CHF 420 à 900', 'azurine' => 44900],
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
            'description' => 'Tous les prix Azurine en francs suisses, TVA incluse : maquillage dès CHF 14, montres dès CHF 149, livraison offerte dès CHF 80, retours gratuits 30 jours.',
            'bodyClass' => 'page-pricing',
        ]);
    }
}
