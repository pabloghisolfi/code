<?php

declare(strict_types=1);

namespace Daris\Catalog;

use Daris\Exception\ConfigurationException;

/**
 * Accès au catalogue (catégories et produits) stocké en JSON dans data/.
 *
 * Les fichiers sont lus une seule fois par requête et validés à la volée.
 */
final class CatalogRepository
{
    /** @var list<Product>|null */
    private ?array $products = null;

    /** @var list<Category>|null */
    private ?array $categories = null;

    public function __construct(private readonly string $dataDir)
    {
    }

    /**
     * @return list<Category>
     */
    public function categories(): array
    {
        if ($this->categories === null) {
            $this->categories = array_map(
                static fn (array $row): Category => Category::fromArray($row),
                $this->readJson('categories.json')
            );
        }

        return $this->categories;
    }

    public function category(string $slug): ?Category
    {
        foreach ($this->categories() as $category) {
            if ($category->slug() === $slug) {
                return $category;
            }
        }

        return null;
    }

    /**
     * @return list<Product>
     */
    public function all(): array
    {
        if ($this->products === null) {
            $this->products = array_map(
                static fn (array $row): Product => Product::fromArray($row),
                $this->readJson('products.json')
            );
        }

        return $this->products;
    }

    public function bySlug(string $slug): ?Product
    {
        foreach ($this->all() as $product) {
            if ($product->slug() === $slug) {
                return $product;
            }
        }

        return null;
    }

    public function byId(int $id): ?Product
    {
        foreach ($this->all() as $product) {
            if ($product->id() === $id) {
                return $product;
            }
        }

        return null;
    }

    /**
     * @param list<string> $slugs
     * @return list<Product>
     */
    public function bySlugs(array $slugs): array
    {
        $found = [];
        foreach ($slugs as $slug) {
            $product = $this->bySlug($slug);
            if ($product !== null) {
                $found[] = $product;
            }
        }

        return $found;
    }

    /**
     * @return list<Product>
     */
    public function byCategory(string $category): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (Product $p): bool => $p->category() === $category
        ));
    }

    /**
     * @return list<Product>
     */
    public function featured(int $limit = 8, ?string $category = null): array
    {
        $items = array_filter(
            $this->all(),
            static fn (Product $p): bool => $p->isFeatured() && ($category === null || $p->category() === $category)
        );

        return array_slice(array_values($items), 0, $limit);
    }

    /**
     * @return list<Product>
     */
    public function withBadge(string $badge, int $limit = 4): array
    {
        $items = array_filter($this->all(), static fn (Product $p): bool => $p->badge() === $badge);

        return array_slice(array_values($items), 0, $limit);
    }

    /**
     * Produits proches : même sous-catégorie d'abord, puis même catégorie.
     *
     * @return list<Product>
     */
    public function related(Product $product, int $limit = 4): array
    {
        $sameBrand = [];
        $same = [];
        $others = [];
        foreach ($this->all() as $candidate) {
            if ($candidate->id() === $product->id() || $candidate->category() !== $product->category()) {
                continue;
            }
            if (strcasecmp($candidate->brand(), $product->brand()) === 0) {
                $sameBrand[] = $candidate;
            } elseif ($candidate->subcategory() === $product->subcategory()) {
                $same[] = $candidate;
            } else {
                $others[] = $candidate;
            }
        }

        return array_slice(array_merge($sameBrand, $same, $others), 0, $limit);
    }

    /**
     * @return list<string>
     */
    public function brands(?string $category = null): array
    {
        $brands = [];
        foreach ($this->all() as $product) {
            if ($category !== null && $product->category() !== $category) {
                continue;
            }
            $brands[$product->brand()] = true;
        }
        $list = array_keys($brands);
        natcasesort($list);

        return array_values($list);
    }

    /**
     * @return list<Product>
     */
    public function search(ProductFilter $filter): array
    {
        return $filter->apply($this->all());
    }

    /**
     * Bornes de prix d'une catégorie (ou du catalogue entier), en centimes.
     *
     * @return array{min: int, max: int}
     */
    public function priceBounds(?string $category = null): array
    {
        $prices = array_map(
            static fn (Product $p): int => $p->price(),
            $category === null ? $this->all() : $this->byCategory($category)
        );

        return ['min' => $prices === [] ? 0 : min($prices), 'max' => $prices === [] ? 0 : max($prices)];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readJson(string $file): array
    {
        $path = $this->dataDir . '/' . $file;
        $raw = is_file($path) ? file_get_contents($path) : false;
        if ($raw === false) {
            throw new ConfigurationException(sprintf('Fichier de données introuvable : %s', $file));
        }

        $decoded = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new ConfigurationException(sprintf('Fichier de données invalide : %s', $file));
        }

        return array_values(array_filter($decoded, 'is_array'));
    }
}
