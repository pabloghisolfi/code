<?php

declare(strict_types=1);

namespace Daris\Config;

use Daris\Exception\ConfigurationException;

/**
 * Accès typé et en lecture seule à la configuration (notation pointée).
 */
final class Config
{
    /**
     * @param array<string, mixed> $values
     */
    public function __construct(private readonly array $values)
    {
    }

    public function string(string $key, string $default = ''): string
    {
        $value = $this->find($key);

        return is_scalar($value) ? (string) $value : $default;
    }

    public function int(string $key, int $default = 0): int
    {
        $value = $this->find($key);

        return is_int($value) ? $value : $default;
    }

    public function float(string $key, float $default = 0.0): float
    {
        $value = $this->find($key);

        return is_int($value) || is_float($value) ? (float) $value : $default;
    }

    public function bool(string $key, bool $default = false): bool
    {
        $value = $this->find($key);

        return is_bool($value) ? $value : $default;
    }

    /**
     * @return array<mixed>
     */
    public function array(string $key): array
    {
        $value = $this->find($key);

        return is_array($value) ? $value : [];
    }

    /**
     * Comme string(), mais refuse une valeur vide (secret, chemin obligatoire…).
     */
    public function required(string $key): string
    {
        $value = $this->string($key);
        if ($value === '') {
            throw new ConfigurationException(sprintf('La clé de configuration « %s » est obligatoire.', $key));
        }

        return $value;
    }

    private function find(string $key): mixed
    {
        $node = $this->values;
        foreach (explode('.', $key) as $segment) {
            if (!is_array($node) || !array_key_exists($segment, $node)) {
                return null;
            }
            $node = $node[$segment];
        }

        return $node;
    }
}
