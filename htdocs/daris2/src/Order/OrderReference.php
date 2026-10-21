<?php

declare(strict_types=1);

namespace Daris\Order;

/**
 * Génération des références de commande (DA-2026-7K3M9Q) et des jetons
 * d'accès au récapitulatif.
 */
final class OrderReference
{
    private const ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    private function __construct()
    {
    }

    public static function generate(): string
    {
        $suffix = '';
        for ($i = 0; $i < 6; $i++) {
            $suffix .= self::ALPHABET[random_int(0, strlen(self::ALPHABET) - 1)];
        }

        return 'DA-' . date('Y') . '-' . $suffix;
    }

    public static function token(): string
    {
        return bin2hex(random_bytes(20));
    }

    public static function isValid(string $reference): bool
    {
        return preg_match('/^DA-\d{4}-[A-Z2-9]{6}$/', $reference) === 1;
    }
}
