<?php

declare(strict_types=1);

namespace Daris\Catalog;

/**
 * Produit du catalogue. Les prix sont en centimes.
 */
final class Product
{
    public const BADGE_LABELS = [
        'nouveau' => 'Nouveau',
        'best-seller' => 'Best-seller',
        'promo' => 'Promo',
    ];

    /**
     * @param list<string>         $details
     * @param list<ProductVariant> $variants
     * @param list<string>         $tags
     * @param array{kind: string, color: string, bg: string} $art
     */
    public function __construct(
        private readonly int $id,
        private readonly string $slug,
        private readonly string $brand,
        private readonly string $name,
        private readonly string $category,
        private readonly string $subcategory,
        private readonly int $price,
        private readonly ?int $oldPrice,
        private readonly ?string $badge,
        private readonly bool $featured,
        private readonly string $short,
        private readonly string $description,
        private readonly array $details,
        private readonly string $variantLabel,
        private readonly array $variants,
        private readonly array $art,
        private readonly int $stock,
        private readonly float $rating,
        private readonly int $reviews,
        private readonly array $tags
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $variants = [];
        $variantLabel = '';
        if (is_array($data['variants'] ?? null)) {
            $variantLabel = (string) ($data['variants']['label'] ?? 'Option');
            foreach ((array) ($data['variants']['options'] ?? []) as $option) {
                if (is_array($option)) {
                    $variants[] = ProductVariant::fromArray($option);
                }
            }
        }

        $art = is_array($data['art'] ?? null) ? $data['art'] : [];
        $color = (string) ($art['color'] ?? '#bcdcf5');
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $color) !== 1) {
            $color = '#bcdcf5';
        }

        $badge = isset($data['badge']) && is_string($data['badge']) && isset(self::BADGE_LABELS[$data['badge']])
            ? $data['badge']
            : null;

        return new self(
            (int) ($data['id'] ?? 0),
            (string) ($data['slug'] ?? ''),
            (string) ($data['brand'] ?? 'Azurine'),
            (string) ($data['name'] ?? ''),
            (string) ($data['category'] ?? ''),
            (string) ($data['subcategory'] ?? ''),
            (int) ($data['price'] ?? 0),
            isset($data['old_price']) ? (int) $data['old_price'] : null,
            $badge,
            (bool) ($data['featured'] ?? false),
            (string) ($data['short'] ?? ''),
            (string) ($data['description'] ?? ''),
            array_values(array_map('strval', (array) ($data['details'] ?? []))),
            $variantLabel,
            $variants,
            [
                'kind' => (string) ($art['kind'] ?? 'giftbox'),
                'color' => $color,
                'bg' => ($art['bg'] ?? 'blue') === 'pink' ? 'pink' : 'blue',
            ],
            (int) ($data['stock'] ?? 0),
            (float) ($data['rating'] ?? 0),
            (int) ($data['reviews'] ?? 0),
            array_values(array_map('strval', (array) ($data['tags'] ?? [])))
        );
    }

    public function id(): int
    {
        return $this->id;
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function brand(): string
    {
        return $this->brand;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function category(): string
    {
        return $this->category;
    }

    public function subcategory(): string
    {
        return $this->subcategory;
    }

    public function price(): int
    {
        return $this->price;
    }

    public function oldPrice(): ?int
    {
        return $this->oldPrice;
    }

    public function discountPercent(): int
    {
        if ($this->oldPrice === null || $this->oldPrice <= $this->price) {
            return 0;
        }

        return (int) round((1 - $this->price / $this->oldPrice) * 100);
    }

    public function badge(): ?string
    {
        return $this->badge;
    }

    public function badgeLabel(): string
    {
        return $this->badge === null ? '' : self::BADGE_LABELS[$this->badge];
    }

    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function short(): string
    {
        return $this->short;
    }

    public function description(): string
    {
        return $this->description;
    }

    /**
     * @return list<string>
     */
    public function details(): array
    {
        return $this->details;
    }

    public function variantLabel(): string
    {
        return $this->variantLabel;
    }

    /**
     * @return list<ProductVariant>
     */
    public function variants(): array
    {
        return $this->variants;
    }

    public function hasVariants(): bool
    {
        return $this->variants !== [];
    }

    public function variant(string $id): ?ProductVariant
    {
        foreach ($this->variants as $variant) {
            if ($variant->id() === $id) {
                return $variant;
            }
        }

        return null;
    }

    /**
     * @return array{kind: string, color: string, bg: string}
     */
    public function art(): array
    {
        return $this->art;
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function isLowStock(): bool
    {
        return $this->stock > 0 && $this->stock <= 8;
    }

    public function rating(): float
    {
        return $this->rating;
    }

    public function reviews(): int
    {
        return $this->reviews;
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return $this->tags;
    }

    public function isWatch(): bool
    {
        return $this->category === 'montres';
    }
}
