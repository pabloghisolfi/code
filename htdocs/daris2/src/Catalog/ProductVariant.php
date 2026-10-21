<?php

declare(strict_types=1);

namespace Daris\Catalog;

/**
 * Déclinaison d'un produit (teinte, bracelet, taille…).
 */
final class ProductVariant
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $color
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $color = (string) ($data['color'] ?? '#dddddd');
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color) !== 1) {
            $color = '#dddddd';
        }

        return new self((string) ($data['id'] ?? ''), (string) ($data['name'] ?? ''), $color);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function color(): string
    {
        return $this->color;
    }
}
