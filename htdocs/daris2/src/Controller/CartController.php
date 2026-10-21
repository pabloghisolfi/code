<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Cart\CartItem;
use Daris\Http\Response;
use Daris\Support\Money;

/**
 * Panier : affichage et modifications (formulaires classiques ou fetch JSON).
 */
final class CartController extends AbstractController
{
    public function index(): Response
    {
        $cart = $this->services->cart();
        $catalog = $this->services->catalog();

        return $this->page('cart', [
            'items' => $cart->items(),
            'totals' => $cart->totals(),
            'promo' => $cart->promo(),
            'remainingForFreeShipping' => $cart->remainingForFreeShipping(),
            'suggestions' => $cart->isEmpty() ? $catalog->featured(4) : $catalog->featured(4, 'maquillage'),
            'maxQuantity' => CartItem::MAX_QUANTITY,
        ], [
            'title' => 'Votre panier – Daris',
            'description' => 'Récapitulatif de votre panier Daris.',
            'noindex' => true,
            'bodyClass' => 'page-cart',
        ]);
    }

    public function add(): Response
    {
        $this->assertCsrf();
        $request = $this->services->request();
        $product = $this->services->catalog()->byId($request->inputInt('product_id', 0, 0, 100000));

        if ($product === null || !$product->isInStock()) {
            return $this->respond(false, 'Ce produit n\'est pas disponible.', '/boutique');
        }

        $variantId = $request->input('variant', '', 60);
        if ($product->hasVariants() && $product->variant($variantId) === null) {
            return $this->respond(false, 'Choisissez une ' . mb_strtolower($product->variantLabel()) . ' avant d\'ajouter au panier.', '/produit/' . $product->slug());
        }

        $quantity = $request->inputInt('quantity', 1, 1, CartItem::MAX_QUANTITY);
        $item = $this->services->cart()->add($product, $product->hasVariants() ? $variantId : null, $quantity);

        return $this->respond(true, $item->label() . ' a rejoint votre panier.', '/panier');
    }

    public function update(): Response
    {
        $this->assertCsrf();
        $request = $this->services->request();
        $key = $request->input('key', '', 80);
        $quantity = $request->inputInt('quantity', 1, 0, CartItem::MAX_QUANTITY);

        $this->services->cart()->update($key, $quantity);

        return $this->respond(true, $quantity === 0 ? 'Article retiré.' : 'Quantité mise à jour.', '/panier');
    }

    public function remove(): Response
    {
        $this->assertCsrf();
        $this->services->cart()->remove($this->services->request()->input('key', '', 80));

        return $this->respond(true, 'Article retiré du panier.', '/panier');
    }

    public function promo(): Response
    {
        $this->assertCsrf();
        $this->assertRateLimit('promo', 10);
        $request = $this->services->request();
        $cart = $this->services->cart();

        if ($request->input('action', '', 10) === 'remove') {
            $cart->removePromo();

            return $this->respond(true, 'Code retiré.', '/panier');
        }

        $promo = $cart->applyPromo($request->input('code', '', 30));
        if ($promo === null) {
            return $this->respond(false, 'Ce code n\'est pas valable.', '/panier');
        }

        return $this->respond(true, 'Code appliqué : ' . $promo->label() . '.', '/panier');
    }

    private function respond(bool $ok, string $message, string $redirectPath): Response
    {
        if ($this->wantsJson()) {
            $cart = $this->services->cart();
            $totals = $cart->totals();

            return Response::json([
                'ok' => $ok,
                'message' => $message,
                'count' => $cart->count(),
                'subtotal' => Money::format($totals->subtotal()),
                'total' => Money::format($totals->total()),
                'shipping' => $totals->shipping() === 0 ? 'Offerte' : Money::format($totals->shipping()),
                'discount' => $totals->discount() > 0 ? '− ' . Money::format($totals->discount()) : '',
                'remaining' => $cart->remainingForFreeShipping(),
                'cartUrl' => $this->services->url()->to('/panier'),
            ], $ok ? 200 : 422);
        }

        $this->flash($ok ? 'success' : 'error', $message);

        return $this->redirect($redirectPath);
    }
}
