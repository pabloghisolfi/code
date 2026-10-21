<?php

declare(strict_types=1);

namespace Daris\Order;

/**
 * Commande confirmée, telle que stockée (lignes et montants figés).
 */
final class Order
{
    public const STATUS_LABELS = [
        'confirmee' => 'Confirmée',
        'preparation' => 'En préparation',
        'expediee' => 'Expédiée',
        'livree' => 'Livrée',
        'annulee' => 'Annulée',
    ];

    /**
     * @param list<array{product_id: int, slug: string, name: string, variant: ?string, quantity: int, unit_price: int, total: int}> $items
     * @param array<string, int|float|string|null> $totals
     */
    public function __construct(
        private readonly string $reference,
        private readonly Customer $customer,
        private readonly array $items,
        private readonly array $totals,
        private readonly string $paymentMethod,
        private readonly ?string $paymentReference,
        private readonly string $status,
        private readonly string $createdAt
    ) {
    }

    public function reference(): string
    {
        return $this->reference;
    }

    public function customer(): Customer
    {
        return $this->customer;
    }

    /**
     * @return list<array{product_id: int, slug: string, name: string, variant: ?string, quantity: int, unit_price: int, total: int}>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function itemCount(): int
    {
        return array_sum(array_column($this->items, 'quantity'));
    }

    /**
     * @return array<string, int|float|string|null>
     */
    public function totals(): array
    {
        return $this->totals;
    }

    public function total(): int
    {
        return (int) ($this->totals['total'] ?? 0);
    }

    public function paymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function paymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function createdAt(): string
    {
        return $this->createdAt;
    }
}
