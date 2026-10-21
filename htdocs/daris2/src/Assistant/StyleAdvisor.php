<?php

declare(strict_types=1);

namespace Daris\Assistant;

use Daris\Exception\ConfigurationException;
use Daris\Support\Str;

/**
 * Conseillère de style : assistant à base de règles (mots-clés → réponse),
 * sans service externe ni donnée personnelle transmise.
 *
 * Les intentions sont décrites dans data/assistant.json ; la meilleure
 * correspondance est celle qui cumule le plus de mots-clés présents.
 */
final class StyleAdvisor
{
    /** @var array<string, mixed>|null */
    private ?array $data = null;

    public function __construct(private readonly string $dataDir)
    {
    }

    public function greeting(): string
    {
        return (string) ($this->data()['greeting'] ?? '');
    }

    /**
     * @return list<string>
     */
    public function suggestions(): array
    {
        return array_values(array_map('strval', (array) ($this->data()['suggestions'] ?? [])));
    }

    /**
     * @return array{answer: string, links: list<array{label: string, path: string}>, matched: bool}
     */
    public function reply(string $question): array
    {
        $needle = ' ' . Str::searchable($question) . ' ';
        $best = null;
        $bestScore = 0;

        foreach ((array) ($this->data()['intents'] ?? []) as $intent) {
            if (!is_array($intent)) {
                continue;
            }
            $score = 0;
            foreach ((array) ($intent['keywords'] ?? []) as $keyword) {
                $normalized = Str::searchable((string) $keyword);
                if ($normalized !== '' && str_contains($needle, ' ' . $normalized . ' ')) {
                    $score += strlen($normalized) > 6 ? 2 : 1;
                } elseif ($normalized !== '' && str_contains($needle, $normalized)) {
                    $score += 1;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $intent;
            }
        }

        if ($best === null) {
            return ['answer' => (string) ($this->data()['fallback'] ?? ''), 'links' => [['label' => 'Nous écrire', 'path' => '/contact']], 'matched' => false];
        }

        $links = [];
        foreach ((array) ($best['links'] ?? []) as $link) {
            if (is_array($link) && isset($link['label'], $link['path']) && str_starts_with((string) $link['path'], '/')) {
                $links[] = ['label' => (string) $link['label'], 'path' => (string) $link['path']];
            }
        }

        return ['answer' => (string) ($best['answer'] ?? ''), 'links' => $links, 'matched' => true];
    }

    /**
     * @return array<string, mixed>
     */
    private function data(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        $path = $this->dataDir . '/assistant.json';
        $raw = is_file($path) ? file_get_contents($path) : false;
        if ($raw === false) {
            throw new ConfigurationException('Fichier de données introuvable : assistant.json');
        }
        $decoded = json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
        $this->data = is_array($decoded) ? $decoded : [];

        return $this->data;
    }
}
