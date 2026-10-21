<?php

declare(strict_types=1);

namespace Daris\Catalog;

/**
 * Inspiration (look) : une occasion, des étapes et les produits utilisés.
 */
final class Look
{
    /**
     * @param list<array{title: string, text: string}> $steps
     * @param list<string>                             $productSlugs
     * @param list<string>                             $palette
     */
    public function __construct(
        private readonly string $slug,
        private readonly string $title,
        private readonly string $occasion,
        private readonly string $duration,
        private readonly string $level,
        private readonly string $excerpt,
        private readonly string $intro,
        private readonly array $steps,
        private readonly array $productSlugs,
        private readonly array $palette,
        private readonly string $art
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $steps = [];
        foreach ((array) ($data['steps'] ?? []) as $step) {
            if (is_array($step)) {
                $steps[] = ['title' => (string) ($step['title'] ?? ''), 'text' => (string) ($step['text'] ?? '')];
            }
        }

        $palette = array_values(array_filter(
            array_map('strval', (array) ($data['palette'] ?? [])),
            static fn (string $c): bool => preg_match('/^#[0-9a-fA-F]{6}$/', $c) === 1
        ));

        return new self(
            (string) ($data['slug'] ?? ''),
            (string) ($data['title'] ?? ''),
            (string) ($data['occasion'] ?? ''),
            (string) ($data['duration'] ?? ''),
            (string) ($data['level'] ?? ''),
            (string) ($data['excerpt'] ?? ''),
            (string) ($data['intro'] ?? ''),
            $steps,
            array_values(array_map('strval', (array) ($data['products'] ?? []))),
            $palette,
            (string) ($data['art'] ?? 'lipstick')
        );
    }

    public function slug(): string
    {
        return $this->slug;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function occasion(): string
    {
        return $this->occasion;
    }

    public function duration(): string
    {
        return $this->duration;
    }

    public function level(): string
    {
        return $this->level;
    }

    public function excerpt(): string
    {
        return $this->excerpt;
    }

    public function intro(): string
    {
        return $this->intro;
    }

    /**
     * @return list<array{title: string, text: string}>
     */
    public function steps(): array
    {
        return $this->steps;
    }

    /**
     * @return list<string>
     */
    public function productSlugs(): array
    {
        return $this->productSlugs;
    }

    /**
     * @return list<string>
     */
    public function palette(): array
    {
        return $this->palette;
    }

    public function art(): string
    {
        return $this->art;
    }
}
