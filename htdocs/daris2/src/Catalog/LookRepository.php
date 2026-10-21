<?php

declare(strict_types=1);

namespace Daris\Catalog;

use Daris\Exception\ConfigurationException;

/**
 * Inspirations (looks) stockées dans data/looks.json.
 */
final class LookRepository
{
    /** @var list<Look>|null */
    private ?array $looks = null;

    public function __construct(private readonly string $dataDir)
    {
    }

    /**
     * @return list<Look>
     */
    public function all(): array
    {
        if ($this->looks !== null) {
            return $this->looks;
        }

        $path = $this->dataDir . '/looks.json';
        $raw = is_file($path) ? file_get_contents($path) : false;
        if ($raw === false) {
            throw new ConfigurationException('Fichier de données introuvable : looks.json');
        }

        $decoded = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
        $this->looks = array_map(
            static fn (array $row): Look => Look::fromArray($row),
            array_values(array_filter(is_array($decoded) ? $decoded : [], 'is_array'))
        );

        return $this->looks;
    }

    public function bySlug(string $slug): ?Look
    {
        foreach ($this->all() as $look) {
            if ($look->slug() === $slug) {
                return $look;
            }
        }

        return null;
    }

    /**
     * Looks qui utilisent un produit donné.
     *
     * @return list<Look>
     */
    public function usingProduct(string $productSlug, int $limit = 3): array
    {
        $found = array_filter(
            $this->all(),
            static fn (Look $look): bool => in_array($productSlug, $look->productSlugs(), true)
        );

        return array_slice(array_values($found), 0, $limit);
    }

    /**
     * @return list<Look>
     */
    public function except(string $slug, int $limit = 3): array
    {
        $others = array_filter($this->all(), static fn (Look $look): bool => $look->slug() !== $slug);

        return array_slice(array_values($others), 0, $limit);
    }
}
