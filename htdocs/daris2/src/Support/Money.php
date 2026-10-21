<?php

declare(strict_types=1);

namespace Daris\Support;

/**
 * Montants en centimes (entiers) et affichage à la suisse : CHF 1’290.00
 */
final class Money
{
    private const THOUSANDS_SEPARATOR = '’';

    private function __construct()
    {
    }

    /**
     * Formate un montant en centimes : 12990 → « CHF 129.90 ».
     */
    public static function format(int $cents, string $currency = 'CHF', bool $withCurrency = true): string
    {
        $sign = $cents < 0 ? '−' : '';
        $cents = abs($cents);
        $units = intdiv($cents, 100);
        $decimals = $cents % 100;

        $amount = number_format($units, 0, '.', self::THOUSANDS_SEPARATOR) . '.' . str_pad((string) $decimals, 2, '0', STR_PAD_LEFT);

        return ($withCurrency ? $currency . ' ' : '') . $sign . $amount;
    }

    /**
     * Montant en francs avec deux décimales, pour les données structurées (« 129.90 »).
     */
    public static function decimal(int $cents): string
    {
        return number_format($cents / 100, 2, '.', '');
    }

    /**
     * Part de TVA incluse dans un montant TTC (taux en pour-cent).
     */
    public static function includedVat(int $grossCents, float $ratePercent): int
    {
        return (int) round($grossCents - $grossCents / (1 + $ratePercent / 100));
    }

    /**
     * Pourcentage d'un montant, arrondi au centime.
     */
    public static function percent(int $cents, float $percent): int
    {
        return (int) round($cents * $percent / 100);
    }

    /**
     * Arrondi commercial suisse aux 5 centimes.
     */
    public static function roundToFiveCents(int $cents): int
    {
        return (int) (round($cents / 5) * 5);
    }
}
