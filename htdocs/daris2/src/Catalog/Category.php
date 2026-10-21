<?php

declare(strict_types=1);

namespace Daris\Catalog;

/**
 * Catégorie principale (maquillage, montres) et ses sous-catégories.
 */
final class Category
{
    /**
     * @param list<array{slug: string, name: string}> $subcategories
     */
    public function __construct(
        private readonly string $slug,
        private readonly string $name,
        private readonly string $title,
        private readonly string $intro,
        private readonly string $icon,
        private readonly string $accent,
        private readonly array $subcategories
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $subs = [];
        foreach ((array) ($data['subcategories'] ?? []) as $sub) {
            if (is_array($sub) && isset($sub['slug'], $sub['name'])) {
                $subs[] = ['slug' => (string) $sub['slug'], 'name' => (string) $sub['name']];
            }
        }

        return new self(
            (string) ($data['slug'] ?? ''),
            (string) ($data['name'] ?? ''),
            (string) ($data['title'] ?? ''),
            (string) ($data['intro'] ?? ''),
            (string) ($data['icon'] ?? 'star'),
            (string) ($data['accent'] ?? 'blue'),
            $subs
        );
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function intro(): string
    {
        return $this->intro;
    }

    public function icon(): string
    {
        return $this->icon;
    }

    public function accent(): string
    {
        return $this->accent;
    }

    /**
     * @return list<array{slug: string, name: string}>
     */
    public function subcategories(): array
    {
        return $this->subcategories;
    }

    public function subcategoryName(string $slug): ?string
    {
        foreach ($this->subcategories as $sub) {
            if ($sub['slug'] === $slug) {
                return $sub['name'];
            }
        }

        return null;
    }

    public function hasSubcategory(string $slug): bool
    {
        return $this->subcategoryName($slug) !== null;
    }
}
