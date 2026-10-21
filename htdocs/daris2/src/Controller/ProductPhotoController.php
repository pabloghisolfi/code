<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Exception\NotFoundException;
use Daris\Http\Response;
use Daris\Support\Str;

/**
 * Vue photo intégrée au site (pas d’image nue sans navigation).
 */
final class ProductPhotoController extends AbstractController
{
    public function show(string $slug): Response
    {
        if (!Str::isSlug($slug)) {
            throw new NotFoundException();
        }

        $product = $this->services->catalog()->bySlug($slug);
        if ($product === null) {
            throw new NotFoundException('Ce produit n\'existe pas ou n\'est plus proposé.');
        }

        return $this->page('product-photo', [
            'product' => $product,
            'category' => $this->services->catalog()->category($product->category()),
        ], [
            'title' => $product->name() . ' – photo – Azurine',
            'description' => $product->short(),
            'noindex' => true,
            'bodyClass' => 'page-product-photo',
        ]);
    }
}
