<?php

declare(strict_types=1);

namespace Daris\Cart;

use Daris\Support\Money;

/**
 * Détail des montants d'un panier ou d'une commande (centimes, TVA incluse).
 */
final class CartTotals
{
    public function __construct(
        private readonly int $subtotal,
        private readonly int $discount,
        private readonly int $shipping,
        private readonly int $giftWrap,
        private readonly float $vatRate,
        private readonly string $shippingLabel,
        private readonly ?string $promoLabel
    ) {
    }

    public function subtotal(): int
    {
        return $this->subtotal;
    }

    public function discount(): int
    {
        return $this->discount;
    }

    public function shipping(): int
    {
        return $this->shipping;
    }

    public function giftWrap(): int
    {
        return $this->giftWrap;
    }

    public function total(): int
    {
        return max(0, $this->subtotal - $this->discount + $this->shipping + $this->giftWrap);
    }

    public function vatIncluded(): int
    {
        return Money::includedVat($this->total(), $this->vatRate);
    }

    public function vatRate(): float
    {
        return $this->vatRate;
    }

    public function shippingLabel(): string
    {
        return $this->shippingLabel;
    }

    public function promoLabel(): ?string
    {
        return $this->promoLabel;
    }

    /**
     * @return array<string, int|float|string|null>
     */
    public function toArray(): array
    {
        return [
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'shipping' => $this->shipping,
            'shipping_label' => $this->shippingLabel,
            'gift_wrap' => $this->giftWrap,
            'vat_rate' => $this->vatRate,
            'vat_included' => $this->vatIncluded(),
            'total' => $this->total(),
            'promo' => $this->promoLabel,
        ];
    }
}
