<?php

declare(strict_types=1);

namespace Daris\Cart;

/**
 * Code promotionnel : pourcentage sur les articles ou livraison offerte.
 */
final class PromoCode
{
    public const TYPE_PERCENT = 'percent';
    public const TYPE_SHIPPING = 'shipping';

    public function __construct(
        private readonly string $code,
        private readonly string $type,
        private readonly int $value,
        private readonly string $label
    ) {
    }

    /**
     * @param array<string, array{type: string, value: int, label: string}> $definitions
     */
    public static function find(string $code, array $definitions): ?self
    {
        $normalized = strtoupper(trim($code));
        if ($normalized === '' || !isset($definitions[$normalized])) {
            return null;
        }
        $def = $definitions[$normalized];

        return new self($normalized, $def['type'], $def['value'], $def['label']);
    }

    public function code(): string
    {
        return $this->code;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function discountOn(int $subtotal): int
    {
        return $this->type === self::TYPE_PERCENT ? (int) round($subtotal * $this->value / 100) : 0;
    }

    public function freeShipping(): bool
    {
        return $this->type === self::TYPE_SHIPPING;
    }
}
