<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Catalog\ProductFilter;
use Daris\Exception\NotFoundException;
use Daris\Http\Response;

/**
 * Catalogue : liste complète, par catégorie, recherche et filtres.
 */
final class CatalogController extends AbstractController
{
    private const PER_PAGE = 12;

    public function index(): Response
    {
        return $this->listing(null);
    }

    public function category(string $slug): Response
    {
        if ($this->services->catalog()->category($slug) === null) {
            throw new NotFoundException('Cette catégorie n\'existe pas.');
        }

        return $this->listing($slug);
    }

    public function search(): Response
    {
        $q = $this->services->request()->query('q', '', 80);

        return Response::redirect($this->services->url()->to('/boutique', $q === '' ? [] : ['q' => $q]));
    }

    private function listing(?string $categorySlug): Response
    {
        $catalog = $this->services->catalog();
        $request = $this->services->request();
        $category = $categorySlug !== null ? $catalog->category($categorySlug) : null;
        $filter = ProductFilter::fromRequest($request, $categorySlug);

        $products = $catalog->search($filter);
        $total = count($products);
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $pageNumber = $request->queryInt('page', 1, 1, $pages);
        $products = array_slice($products, ($pageNumber - 1) * self::PER_PAGE, self::PER_PAGE);

        $basePath = $categorySlug === null ? '/boutique' : '/boutique/' . $categorySlug;
        $title = $category !== null ? $category->name() : 'Toute la boutique';
        if ($filter->query() !== '') {
            $title = 'Recherche : « ' . $filter->query() . ' »';
        } elseif ($category !== null && $filter->subcategory() !== null) {
            $title = $category->subcategoryName($filter->subcategory()) ?? $title;
        }

        return $this->page('catalog', [
            'category' => $category,
            'categories' => $catalog->categories(),
            'filter' => $filter,
            'products' => $products,
            'total' => $total,
            'pageNumber' => $pageNumber,
            'pages' => $pages,
            'basePath' => $basePath,
            'title' => $title,
            'sorts' => ProductFilter::SORTS,
            'priceRanges' => ProductFilter::PRICE_RANGES,
            'brands' => $catalog->brands($categorySlug),
        ], [
            'title' => ($category !== null ? $category->name() : 'Boutique') . ' – Azurine, multimarque CHF',
            'description' => $category !== null ? $category->intro() : 'Catalogue multimarque Azurine : grandes maisons de maquillage et montres d\'exception, prix suisses en CHF.',
            'noindex' => $filter->isActive() || $pageNumber > 1,
            'canonical' => $this->services->url()->absolute($basePath),
            'bodyClass' => 'page-catalog',
        ]);
    }
}
