<?php

declare(strict_types=1);

namespace Daris\Support;

/**
 * Petits utilitaires de chaînes.
 */
final class Str
{
    private function __construct()
    {
    }

    /**
     * Échappement HTML systématique (attributs et texte).
     */
    public static function e(string|int|float|null $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }

    /**
     * Transforme un texte en identifiant d'URL : « Rouge Pétale » → rouge-petale
     */
    public static function slug(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $translit = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = $translit !== false ? $translit : $value;
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';

        return trim($value, '-');
    }

    /**
     * Vrai si la valeur ressemble à un slug sûr (lettres minuscules, chiffres, tirets).
     */
    public static function isSlug(string $value): bool
    {
        return preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) === 1 && strlen($value) <= 80;
    }

    /**
     * Coupe un texte au mot le plus proche, avec points de suspension.
     */
    public static function excerpt(string $text, int $length = 140): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', strip_tags($text)) ?? '');
        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $cut = mb_substr($text, 0, $length);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space !== false ? mb_substr($cut, 0, $space) : $cut, ' ,;:.') . '…';
    }

    /**
     * Normalise un texte pour la recherche : minuscules, sans accents ni ponctuation.
     */
    public static function searchable(string $value): string
    {
        $value = mb_strtolower($value);
        $translit = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $value = $translit !== false ? $translit : $value;

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '');
    }
}
