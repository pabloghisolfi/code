<?php

declare(strict_types=1);

namespace Daris\I18n;

/**
 * Chaînes d’interface (pas les noms de produits).
 */
final class Translator
{
    /** @var array<string, array<string, string>>|null */
    private static ?array $messages = null;

    public function __construct(private readonly string $locale)
    {
    }

    public function locale(): string
    {
        return $this->locale;
    }

    public function t(string $key, string $fallback = ''): string
    {
        $all = self::catalog();
        $value = $all[$this->locale][$key] ?? $all[Locale::DEFAULT][$key] ?? $fallback;

        return $value !== '' ? $value : ($fallback !== '' ? $fallback : $key);
    }

    /**
     * @return array<string, array<string, string>>
     */
    private static function catalog(): array
    {
        if (self::$messages !== null) {
            return self::$messages;
        }

        $path = dirname(__DIR__, 2) . '/lang/messages.php';
        /** @var array<string, array<string, string>> $loaded */
        $loaded = is_file($path) ? require $path : [];

        return self::$messages = $loaded;
    }
}
