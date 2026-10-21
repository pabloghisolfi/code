<?php

declare(strict_types=1);

namespace Daris\Config;

/**
 * Chargeur minimaliste de fichier .env (clé=valeur, commentaires avec #).
 *
 * Les variables déjà présentes dans l'environnement du serveur ont priorité
 * sur celles du fichier.
 */
final class Env
{
    /** @var array<string, string> */
    private static array $values = [];

    private function __construct()
    {
    }

    /**
     * Lit le fichier s'il existe. Son absence n'est pas une erreur :
     * les valeurs par défaut s'appliquent alors.
     */
    public static function load(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            if (preg_match('/^[A-Z][A-Z0-9_]*$/', $key) !== 1) {
                continue;
            }

            self::$values[$key] = self::unquote(trim($value));
        }
    }

    public static function get(string $key, string $default = ''): string
    {
        $server = getenv($key);
        if (is_string($server) && $server !== '') {
            return $server;
        }

        return self::$values[$key] ?? $default;
    }

    public static function bool(string $key, bool $default): bool
    {
        $raw = strtolower(self::get($key, $default ? 'true' : 'false'));

        return in_array($raw, ['1', 'true', 'yes', 'on'], true);
    }

    public static function int(string $key, int $default): int
    {
        $raw = self::get($key, (string) $default);

        return preg_match('/^-?\d+$/', $raw) === 1 ? (int) $raw : $default;
    }

    private static function unquote(string $value): string
    {
        $length = strlen($value);
        if ($length >= 2 && ($value[0] === '"' || $value[0] === "'") && $value[$length - 1] === $value[0]) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
