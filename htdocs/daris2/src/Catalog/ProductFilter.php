<?php

declare(strict_types=1);

namespace Daris\Catalog;

use Daris\Http\Request;
use Daris\Support\Str;

/**
 * Critères de recherche et de tri du catalogue, construits depuis la requête.
 */
final class ProductFilter
{
    public const SORTS = [
        'pertinence' => 'Pertinence',
        'prix-asc' => 'Prix croissant',
        'prix-desc' => 'Prix décroissant',
        'nouveautes' => 'Nouveautés',
        'notes' => 'Mieux notés',
    ];

    public const PRICE_RANGES = [
        '' => 'Tous les prix',
        '0-2500' => 'Moins de CHF 25',
        '2500-5000' => 'CHF 25 à 50',
        '5000-20000' => 'CHF 50 à 200',
        '20000-' => 'Plus de CHF 200',
    ];

    public function __construct(
        private readonly ?string $category = null,
        private readonly ?string $subcategory = null,
        private readonly string $query = '',
        private readonly ?int $minPrice = null,
        private readonly ?int $maxPrice = null,
        private readonly string $sort = 'pertinence',
        private readonly ?string $badge = null
    ) {
    }

    public static function fromRequest(Request $request, ?string $category = null): self
    {
        $sub = $request->query('sous', '', 40);
        $range = $request->query('prix', '', 20);
        [$min, $max] = self::parseRange($range);
        $sort = $request->query('tri', 'pertinence', 20);
        $badge = $request->query('badge', '', 20);

        return new self(
            $category !== null && Str::isSlug($category) ? $category : null,
            Str::isSlug($sub) ? $sub : null,
            $request->query('q', '', 80),
            $min,
            $max,
            isset(self::SORTS[$sort]) ? $sort : 'pertinence',
            isset(Product::BADGE_LABELS[$badge]) ? $badge : null
        );
    }

    /**
     * @param list<Product> $products
     * @return list<Product>
     */
    public function apply(array $products): array
    {
        $needle = Str::searchable($this->query);
        $terms = $needle === '' ? [] : explode(' ', $needle);
        $scores = [];

        $result = array_values(array_filter($products, function (Product $product) use ($terms, &$scores): bool {
            if ($this->category !== null && $product->category() !== $this->category) {
                return false;
            }
            if ($this->subcategory !== null && $product->subcategory() !== $this->subcategory) {
                return false;
            }
            if ($this->minPrice !== null && $product->price() < $this->minPrice) {
                return false;
            }
            if ($this->maxPrice !== null && $product->price() > $this->maxPrice) {
                return false;
            }
            if ($this->badge !== null && $product->badge() !== $this->badge) {
                return false;
            }
            if ($terms === []) {
                return true;
            }

            $score = $this->score($product, $terms);
            $scores[$product->id()] = $score;

            return $score > 0;
        }));

        usort($result, function (Product $a, Product $b) use ($scores): int {
            return match ($this->sort) {
                'prix-asc' => $a->price() <=> $b->price(),
                'prix-desc' => $b->price() <=> $a->price(),
                'nouveautes' => [$b->badge() === 'nouveau', $b->id()] <=> [$a->badge() === 'nouveau', $a->id()],
                'notes' => [$b->rating(), $b->reviews()] <=> [$a->rating(), $a->reviews()],
                default => $scores === []
                    ? [$b->isFeatured(), $b->reviews()] <=> [$a->isFeatured(), $a->reviews()]
                    : ($scores[$b->id()] ?? 0) <=> ($scores[$a->id()] ?? 0),
            };
        });

        return $result;
    }

    public function category(): ?string
    {
        return $this->category;
    }

    public function subcategory(): ?string
    {
        return $this->subcategory;
    }

    public function query(): string
    {
        return $this->query;
    }

    public function sort(): string
    {
        return $this->sort;
    }

    public function badge(): ?string
    {
        return $this->badge;
    }

    public function priceRange(): string
    {
        foreach (array_keys(self::PRICE_RANGES) as $key) {
            if ($key === '') {
                continue;
            }
            [$min, $max] = self::parseRange($key);
            if ($min === $this->minPrice && $max === $this->maxPrice) {
                return $key;
            }
        }

        return '';
    }

    public function isActive(): bool
    {
        return $this->subcategory !== null || $this->query !== '' || $this->minPrice !== null
            || $this->maxPrice !== null || $this->badge !== null || $this->sort !== 'pertinence';
    }

    /**
     * Paramètres de requête à conserver dans les liens (changement de tri, etc.).
     *
     * @return array<string, string>
     */
    public function toQuery(): array
    {
        $query = [];
        if ($this->subcategory !== null) {
            $query['sous'] = $this->subcategory;
        }
        if ($this->query !== '') {
            $query['q'] = $this->query;
        }
        if ($this->priceRange() !== '') {
            $query['prix'] = $this->priceRange();
        }
        if ($this->sort !== 'pertinence') {
            $query['tri'] = $this->sort;
        }
        if ($this->badge !== null) {
            $query['badge'] = $this->badge;
        }

        return $query;
    }

    /**
     * @param list<string> $terms
     */
    private function score(Product $product, array $terms): int
    {
        $name = Str::searchable($product->name());
        $haystack = $name . ' ' . Str::searchable($product->short() . ' ' . $product->category() . ' ' . $product->subcategory()
            . ' ' . implode(' ', $product->tags()) . ' ' . $product->description());

        $score = 0;
        foreach ($terms as $term) {
            if (strlen($term) < 2) {
                continue;
            }
            if (str_contains($name, $term)) {
                $score += 10;
            } elseif (str_contains($haystack, $term)) {
                $score += 3;
            } else {
                return 0;
            }
        }

        return $score;
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private static function parseRange(string $range): array
    {
        if (preg_match('/^(\d{1,6})-(\d{0,6})$/', $range, $m) !== 1) {
            return [null, null];
        }

        return [(int) $m[1], $m[2] === '' ? null : (int) $m[2]];
    }
}
