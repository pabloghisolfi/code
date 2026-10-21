<?php

declare(strict_types=1);

namespace Daris\Cart;

use Daris\Catalog\Product;
use Daris\Catalog\ProductVariant;

/**
 * Ligne du panier : un produit, une déclinaison éventuelle, une quantité.
 */
final class CartItem
{
    public const MAX_QUANTITY = 10;

    public function __construct(
        private readonly Product $product,
        private readonly ?ProductVariant $variant,
        private int $quantity
    ) {
        $this->quantity = max(1, min(self::MAX_QUANTITY, $quantity));
    }

    /**
     * Clé unique de la ligne (produit + déclinaison).
     */
    public static function keyFor(int $productId, ?string $variantId): string
    {
        return $productId . ':' . ($variantId ?? '');
    }

    public function key(): string
    {
        return self::keyFor($this->product->id(), $this->variant?->id());
    }

    public function product(): Product
    {
        return $this->product;
    }

    public function variant(): ?ProductVariant
    {
        return $this->variant;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = max(1, min(self::MAX_QUANTITY, $quantity));
    }

    public function unitPrice(): int
    {
        return $this->product->price();
    }

    public function total(): int
    {
        return $this->product->price() * $this->quantity;
    }

    public function label(): string
    {
        $label = $this->product->name();
        if ($this->variant !== null) {
            $label .= ' · ' . $this->variant->name();
        }

        return $label;
    }

    /**
     * Représentation figée, stockée avec la commande.
     *
     * @return array{product_id: int, slug: string, name: string, variant: ?string, quantity: int, unit_price: int, total: int}
     */
    public function toArray(): array
    {
        return [
            'product_id' => $this->product->id(),
            'slug' => $this->product->slug(),
            'name' => $this->product->name(),
            'variant' => $this->variant?->name(),
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice(),
            'total' => $this->total(),
        ];
    }
}
