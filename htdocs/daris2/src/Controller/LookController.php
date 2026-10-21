<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Exception\NotFoundException;
use Daris\Http\Response;
use Daris\Support\Str;

/**
 * Inspirations : looks complets avec les produits utilisés.
 */
final class LookController extends AbstractController
{
    public function index(): Response
    {
        return $this->page('looks', [
            'looks' => $this->services->looks()->all(),
        ], [
            'title' => 'Inspirations : looks et idées – Daris',
            'description' => 'Des looks complets, étape par étape, pour tous les jours, les cours, les soirées et les cérémonies, avec les produits et la montre assortis.',
            'bodyClass' => 'page-looks',
        ]);
    }

    public function show(string $slug): Response
    {
        $look = Str::isSlug($slug) ? $this->services->looks()->bySlug($slug) : null;
        if ($look === null) {
            throw new NotFoundException('Cette inspiration n\'existe pas.');
        }

        $products = $this->services->catalog()->bySlugs($look->productSlugs());
        $total = array_sum(array_map(static fn ($p): int => $p->price(), $products));

        return $this->page('look', [
            'look' => $look,
            'products' => $products,
            'total' => $total,
            'others' => $this->services->looks()->except($look->slug(), 3),
        ], [
            'title' => $look->title() . ' : le look complet – Daris',
            'description' => $look->excerpt(),
            'bodyClass' => 'page-look',
        ]);
    }

    /**
     * Ajoute tous les produits d'un look (sans déclinaison : la première est choisie).
     */
    public function addAll(string $slug): Response
    {
        $this->assertCsrf();
        $look = Str::isSlug($slug) ? $this->services->looks()->bySlug($slug) : null;
        if ($look === null) {
            throw new NotFoundException();
        }

        $cart = $this->services->cart();
        $added = 0;
        foreach ($this->services->catalog()->bySlugs($look->productSlugs()) as $product) {
            if (!$product->isInStock()) {
                continue;
            }
            $variant = $product->hasVariants() ? $product->variants()[0]->id() : null;
            $cart->add($product, $variant, 1);
            $added++;
        }

        $this->flash('success', sprintf('%d produits du look « %s » sont dans votre panier. Vous pouvez ajuster les teintes.', $added, $look->title()));

        return $this->redirect('/panier');
    }
}
