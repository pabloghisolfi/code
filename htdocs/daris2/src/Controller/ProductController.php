<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Exception\NotFoundException;
use Daris\Http\Response;
use Daris\Support\Money;
use Daris\Support\Str;

/**
 * Fiche produit.
 */
final class ProductController extends AbstractController
{
    public function show(string $slug): Response
    {
        if (!Str::isSlug($slug)) {
            throw new NotFoundException();
        }

        $catalog = $this->services->catalog();
        $product = $catalog->bySlug($slug);
        if ($product === null) {
            throw new NotFoundException('Ce produit n\'existe pas ou n\'est plus proposé.');
        }

        $category = $catalog->category($product->category());
        $session = $this->services->session();
        $previous = array_values(array_filter($session->getArray('viewed_products'), 'is_string'));
        $previous = array_values(array_diff($previous, [$product->slug()]));
        $viewed = array_slice(array_merge([$product->slug()], $previous), 0, 12);
        $session->set('viewed_products', $viewed);

        $url = $this->services->url();

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name(),
            'description' => $product->short(),
            'sku' => 'DA-' . str_pad((string) $product->id(), 4, '0', STR_PAD_LEFT),
            'brand' => ['@type' => 'Brand', 'name' => $product->brand()],
            'category' => $category?->name(),
            'offers' => [
                '@type' => 'Offer',
                'url' => $url->absolute('/produit/' . $product->slug()),
                'priceCurrency' => 'CHF',
                'price' => Money::decimal($product->price()),
                'availability' => $product->isInStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => $product->rating(),
                'reviewCount' => $product->reviews(),
            ],
        ];

        return $this->page('product', [
            'product' => $product,
            'category' => $category,
            'related' => $catalog->relatedFor($product, array_slice($viewed, 1), 4),
            'looks' => $this->services->looks()->usingProduct($product->slug(), 2),
        ], [
            'title' => $product->name() . ' – ' . Money::format($product->price()) . ' – Azurine',
            'description' => Str::excerpt($product->short() . ' ' . $product->description(), 155),
            'ogType' => 'product',
            'bodyClass' => 'page-product',
            'jsonLd' => $jsonLd,
        ]);
    }
}
