<?php

declare(strict_types=1);

namespace Daris\Assistant;

use Daris\Exception\ConfigurationException;

/**
 * Questions fréquentes, groupées par thème (data/faq.json).
 */
final class FaqRepository
{
    /** @var list<array{topic: string, items: list<array{q: string, a: string}>}>|null */
    private ?array $topics = null;

    public function __construct(private readonly string $dataDir)
    {
    }

    /**
     * @return list<array{topic: string, items: list<array{q: string, a: string}>}>
     */
    public function topics(): array
    {
        if ($this->topics !== null) {
            return $this->topics;
        }

        $path = $this->dataDir . '/faq.json';
        $raw = is_file($path) ? file_get_contents($path) : false;
        if ($raw === false) {
            throw new ConfigurationException('Fichier de données introuvable : faq.json');
        }

        $decoded = json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
        $topics = [];
        foreach (is_array($decoded) ? $decoded : [] as $topic) {
            if (!is_array($topic)) {
                continue;
            }
            $items = [];
            foreach ((array) ($topic['items'] ?? []) as $item) {
                if (is_array($item) && isset($item['q'], $item['a'])) {
                    $items[] = ['q' => (string) $item['q'], 'a' => (string) $item['a']];
                }
            }
            $topics[] = ['topic' => (string) ($topic['topic'] ?? ''), 'items' => $items];
        }

        $this->topics = $topics;

        return $topics;
    }

    /**
     * Sélection courte pour la page d'accueil ou les tarifs.
     *
     * @return list<array{q: string, a: string}>
     */
    public function highlights(int $limit = 5): array
    {
        $items = [];
        foreach ($this->topics() as $topic) {
            foreach ($topic['items'] as $item) {
                $items[] = $item;
                if (count($items) >= $limit) {
                    return $items;
                }
            }
        }

        return $items;
    }
}
