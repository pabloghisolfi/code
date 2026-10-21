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
    /** @var array<string, string> clé tri => clé i18n */
    public const SORT_KEYS = [
        'pertinence' => 'sort.pertinence',
        'prix-asc' => 'sort.prix_asc',
        'prix-desc' => 'sort.prix_desc',
        'nouveautes' => 'sort.nouveautes',
        'notes' => 'sort.notes',
    ];

    public const PRICE_RANGES = [
        '' => 'price.all',
        '0-10000' => 'price.under_100',
        '10000-30000' => 'price.100_300',
        '10000-50000' => 'price.100_500',
        '50000-500000' => 'price.500_5000',
        '500000-2000000' => 'price.5000_20000',
        '2000000-' => 'price.over_20000',
    ];

    /**
     * @param list<string> $brands
     */
    public function __construct(
        private readonly ?string $category = null,
        private readonly ?string $subcategory = null,
        private readonly string $query = '',
        private readonly ?int $minPrice = null,
        private readonly ?int $maxPrice = null,
        private readonly string $sort = 'pertinence',
        private readonly ?string $badge = null,
        private readonly array $brands = [],
        private readonly ?int $budgetMinChf = null,
        private readonly ?int $budgetMaxChf = null
    ) {
    }

    public static function fromRequest(Request $request, ?string $category = null): self
    {
        $sub = $request->query('sous', '', 40);
        $range = $request->query('prix', '', 20);
        [$min, $max] = self::parseRange($range);
        $sort = $request->query('tri', 'pertinence', 20);
        $badge = $request->query('badge', '', 20);
        $brands = $request->queryList('marque', 24, 60);

        $budgetMin = $request->queryInt('prix_min', -1, -1, 500_000);
        $budgetMax = $request->queryInt('prix_max', -1, -1, 500_000);
        $budgetMinChf = $budgetMin >= 0 ? $budgetMin : null;
        $budgetMaxChf = $budgetMax >= 0 ? $budgetMax : null;

        if ($budgetMinChf !== null) {
            $min = $budgetMinChf * 100;
        }
        if ($budgetMaxChf !== null) {
            $max = $budgetMaxChf * 100;
        }

        return new self(
            $category !== null && Str::isSlug($category) ? $category : null,
            Str::isSlug($sub) ? $sub : null,
            $request->query('q', '', 80),
            $min,
            $max,
            isset(self::SORT_KEYS[$sort]) ? $sort : 'pertinence',
            isset(Product::BADGE_LABELS[$badge]) ? $badge : null,
            $brands,
            $budgetMinChf,
            $budgetMaxChf
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
            if ($this->brands !== []) {
                $ok = false;
                foreach ($this->brands as $b) {
                    if (strcasecmp($product->brand(), $b) === 0) {
                        $ok = true;
                        break;
                    }
                }
                if (!$ok) {
                    return false;
                }
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

    /**
     * @return list<string>
     */
    public function brands(): array
    {
        return $this->brands;
    }

    public function budgetMinChf(): ?int
    {
        return $this->budgetMinChf;
    }

    public function budgetMaxChf(): ?int
    {
        return $this->budgetMaxChf;
    }

    public function priceRange(): string
    {
        if ($this->budgetMinChf !== null || $this->budgetMaxChf !== null) {
            return '';
        }

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
            || $this->maxPrice !== null || $this->badge !== null || $this->brands !== []
            || $this->budgetMinChf !== null || $this->budgetMaxChf !== null
            || $this->sort !== 'pertinence';
    }

    /**
     * @return array<string, string|list<string>>
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
        if ($this->budgetMinChf !== null) {
            $query['prix_min'] = (string) $this->budgetMinChf;
        }
        if ($this->budgetMaxChf !== null) {
            $query['prix_max'] = (string) $this->budgetMaxChf;
        }
        if ($this->sort !== 'pertinence') {
            $query['tri'] = $this->sort;
        }
        if ($this->badge !== null) {
            $query['badge'] = $this->badge;
        }
        if ($this->brands !== []) {
            $query['marque'] = $this->brands;
        }

        return $query;
    }

    /**
     * @param list<string> $terms
     */
    private function score(Product $product, array $terms): int
    {
        $name = Str::searchable($product->name());
        $haystack = $name . ' ' . Str::searchable($product->brand() . ' ' . $product->short() . ' ' . $product->category() . ' ' . $product->subcategory()
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
        if (preg_match('/^(\d{1,9})-(\d{0,9})$/', $range, $m) !== 1) {
            return [null, null];
        }

        return [(int) $m[1], $m[2] === '' ? null : (int) $m[2]];
    }
}
