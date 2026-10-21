<?php

declare(strict_types=1);

namespace Daris\I18n;

/**
 * Locales prises en charge (session, pas de sous-répertoire d’URL).
 */
final class Locale
{
    public const DEFAULT = 'fr';

    /** @var array<string, string> code => native label */
    public const LABELS = [
        'fr' => 'Français',
        'de' => 'Deutsch',
        'es' => 'Español',
        'ru' => 'Русский',
        'bs' => 'Bosanski',
        'sr' => 'Српски',
        'hr' => 'Hrvatski',
        'ja' => '日本語',
        'zh' => '中文',
        'ar' => 'العربية',
    ];

    public static function isSupported(string $code): bool
    {
        return isset(self::LABELS[$code]);
    }

    public static function normalize(string $code): string
    {
        $code = strtolower(trim($code));

        return self::isSupported($code) ? $code : self::DEFAULT;
    }

    public static function isRtl(string $code): bool
    {
        return $code === 'ar';
    }
}
