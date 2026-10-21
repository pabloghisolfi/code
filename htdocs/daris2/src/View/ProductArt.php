<?php

declare(strict_types=1);

namespace Daris\View;

use Daris\Catalog\Product;
use Daris\Support\Str;

/**
 * Illustrations SVG des produits, générées à la volée.
 *
 * Chaque « kind » (rouge à lèvres, montre ronde, palette…) est un dessin
 * vectoriel paramétré par la couleur du produit : le catalogue garde une
 * identité graphique cohérente sans dépendre de photos.
 */
final class ProductArt
{
    private const BACKGROUNDS = [
        'pink' => ['#fff7fa', '#f9e3ec'],
        'blue' => ['#f3f9fe', '#dceefb'],
    ];

    private const NAVY = '#1f3a68';
    private const GOLD = '#e3b7a5';

    private static int $counter = 0;

    private function __construct()
    {
    }

    /**
     * SVG complet d'un produit (fond + illustration).
     */
    public static function product(Product $product, string $class = 'art'): string
    {
        $art = $product->art();

        return self::render($art['kind'], $art['color'], $art['bg'], $class, $product->name(), $product->brand());
    }

    /**
     * SVG d'après un type et une couleur (looks, catégories, décor).
     */
    public static function render(string $kind, string $color, string $bg = 'blue', string $class = 'art', string $title = '', string $brand = ''): string
    {
        $id = 'a' . (++self::$counter);
        [$from, $to] = self::BACKGROUNDS[$bg] ?? self::BACKGROUNDS['blue'];
        $color = preg_match('/^#[0-9a-fA-F]{6}$/', $color) === 1 ? $color : '#bcdcf5';

        $drawing = match ($kind) {
            'lipstick' => self::lipstick($color, $id),
            'balm' => self::balm($color, $id),
            'gloss' => self::gloss($color, $id),
            'mascara' => self::mascara($color, $id),
            'palette' => self::palette($color, $id),
            'eyeliner' => self::eyeliner($color, $id),
            'brow' => self::brow($color, $id),
            'foundation' => self::foundation($color, $id),
            'concealer' => self::concealer($color, $id),
            'blush' => self::blush($color, $id),
            'highlighter' => self::highlighter($color, $id),
            'spray' => self::spray($color, $id),
            'nailpolish' => self::nailPolish($color, $id),
            'brushes' => self::brushes($color, $id),
            'pouch' => self::pouch($color, $id),
            'giftbox' => self::giftBox($color, $id),
            'watch-square' => self::watchSquare($color, $id),
            'watch-mini' => self::watchMini($color, $id),
            'watch-jewel' => self::watchRound($color, $id, true),
            'watch-pearl' => self::watchPearl($color, $id),
            'watch-chrono' => self::watchChrono($color, $id),
            'strap' => self::strap($color, $id),
            'case' => self::travelCase($color, $id),
            default => self::watchRound($color, $id, false),
        };

        $drawing = str_replace('%%BRAND%%', self::brandSvgText($brand), $drawing);

        $titleTag = $title === '' ? '' : '<title>' . Str::e($title) . '</title>';
        $aria = $title === '' ? ' aria-hidden="true"' : ' role="img"';

        return '<svg class="' . Str::e($class) . '" viewBox="0 0 400 400" xmlns="http://www.w3.org/2000/svg"' . $aria . '>'
            . $titleTag
            . '<defs>'
            . '<radialGradient id="' . $id . 'bg" cx="35%" cy="25%" r="90%"><stop offset="0" stop-color="' . $from . '"/><stop offset="1" stop-color="' . $to . '"/></radialGradient>'
            . '<linearGradient id="' . $id . 'gl" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#ffffff" stop-opacity=".55"/><stop offset=".45" stop-color="#ffffff" stop-opacity="0"/><stop offset="1" stop-color="#000000" stop-opacity=".10"/></linearGradient>'
            . '<linearGradient id="' . $id . 'metal" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset=".5" stop-color="#cfd8e3"/><stop offset="1" stop-color="#9aa9bd"/></linearGradient>'
            . '<linearGradient id="' . $id . 'gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f6e2d8"/><stop offset=".5" stop-color="' . self::GOLD . '"/><stop offset="1" stop-color="#c3907c"/></linearGradient>'
            . '<filter id="' . $id . 'sh" x="-20%" y="-20%" width="140%" height="160%"><feGaussianBlur stdDeviation="10"/></filter>'
            . '</defs>'
            . '<rect width="400" height="400" fill="url(#' . $id . 'bg)"/>'
            . '<ellipse cx="200" cy="332" rx="110" ry="16" fill="' . self::NAVY . '" opacity=".10" filter="url(#' . $id . 'sh)"/>'
            . $drawing
            . '</svg>';
    }

    /** Texte de marque (pas de logo) pour les visuels produits. */
    private static function brandSvgText(string $brand): string
    {
        $brand = trim($brand);
        if ($brand === '') {
            return 'Azurine';
        }

        return Str::e($brand);
    }

    // ---- Maquillage ------------------------------------------------------

    private static function lipstick(string $c, string $id): string
    {
        $dark = self::shade($c, -0.25);

        return '<g transform="translate(0 6)">'
            . '<rect x="150" y="190" width="64" height="130" rx="10" fill="' . self::NAVY . '"/>'
            . '<rect x="150" y="190" width="64" height="130" rx="10" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="156" y="176" width="52" height="22" rx="4" fill="url(#' . $id . 'gold)"/>'
            . '<path d="M166 176 V112 a16 16 0 0 1 16 -16 h4 l22 -24 c6 -4 10 0 10 6 v98 z" fill="' . $c . '"/>'
            . '<path d="M166 176 V112 a16 16 0 0 1 16 -16 h4 l22 -24 c6 -4 10 0 10 6 v98 z" fill="url(#' . $id . 'gl)"/>'
            . '<path d="M186 96 l22 -24 c6 -4 10 0 10 6 v24 l-32 0z" fill="' . $dark . '" opacity=".35"/>'
            . '<rect x="238" y="90" width="64" height="230" rx="10" fill="' . self::NAVY . '"/>'
            . '<rect x="238" y="90" width="64" height="230" rx="10" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="246" y="200" width="48" height="2" fill="url(#' . $id . 'gold)"/>'
            . '</g>';
    }

    private static function balm(string $c, string $id): string
    {
        return '<g>'
            . '<rect x="160" y="150" width="80" height="170" rx="26" fill="' . self::shade($c, 0.35) . '"/>'
            . '<rect x="160" y="150" width="80" height="170" rx="26" fill="url(#' . $id . 'gl)"/>'
            . '<path d="M172 150 V120 a28 28 0 0 1 56 0 V150 z" fill="' . $c . '"/>'
            . '<rect x="160" y="140" width="80" height="18" rx="4" fill="url(#' . $id . 'gold)"/>'
            . '<circle cx="200" cy="248" r="18" fill="none" stroke="' . self::NAVY . '" stroke-opacity=".35" stroke-width="1.5"/>'
            . '<path d="M192 248 h16 M200 240 v16" stroke="' . self::NAVY . '" stroke-opacity=".35" stroke-width="1.5"/>'
            . '</g>';
    }

    private static function gloss(string $c, string $id): string
    {
        return '<g>'
            . '<rect x="170" y="140" width="60" height="180" rx="14" fill="#ffffff" opacity=".7"/>'
            . '<rect x="176" y="190" width="48" height="124" rx="10" fill="' . $c . '" opacity=".85"/>'
            . '<rect x="170" y="140" width="60" height="180" rx="14" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="170" y="140" width="60" height="180" rx="14" fill="none" stroke="' . self::NAVY . '" stroke-opacity=".12"/>'
            . '<rect x="176" y="92" width="48" height="56" rx="8" fill="url(#' . $id . 'gold)"/>'
            . '<rect x="196" y="148" width="8" height="120" fill="#ffffff" opacity=".5"/>'
            . '<ellipse cx="200" cy="272" rx="9" ry="20" fill="' . self::shade($c, -0.2) . '" opacity=".7"/>'
            . '<circle cx="186" cy="112" r="3" fill="#ffffff" opacity=".7"/>'
            . '</g>';
    }

    private static function mascara(string $c, string $id): string
    {
        $brush = '';
        for ($i = 0; $i < 12; $i++) {
            $y = 150 + $i * 11;
            $brush .= '<path d="M248 ' . $y . ' h-22 M248 ' . $y . ' h22" stroke="' . $c . '" stroke-width="2" stroke-linecap="round"/>';
        }

        return '<g>'
            . '<rect x="130" y="110" width="58" height="210" rx="14" fill="' . $c . '"/>'
            . '<rect x="130" y="110" width="58" height="210" rx="14" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="136" y="76" width="46" height="40" rx="8" fill="url(#' . $id . 'gold)"/>'
            . '<text x="159" y="240" font-family="Georgia, serif" font-size="12" letter-spacing="3" fill="#ffffff" opacity=".8" text-anchor="middle" transform="rotate(-90 159 240)">%%BRAND%%</text>'
            . '<rect x="233" y="88" width="30" height="58" rx="6" fill="url(#' . $id . 'gold)"/>'
            . '<rect x="244" y="146" width="8" height="150" rx="4" fill="' . self::NAVY . '"/>'
            . $brush
            . '</g>';
    }

    private static function palette(string $c, string $id): string
    {
        $shades = ['#f7e7dd', '#f2cfc9', '#e8b4bd', $c, '#d99aa8', '#c98a8f', '#efd8bd', '#e3b7a5', '#bcdcf5'];
        $pans = '';
        foreach ($shades as $i => $shade) {
            $x = 136 + ($i % 3) * 64;
            $y = 190 + intdiv($i, 3) * 48;
            $pans .= '<rect x="' . $x . '" y="' . $y . '" width="52" height="36" rx="8" fill="' . $shade . '"/>'
                . '<rect x="' . $x . '" y="' . $y . '" width="52" height="36" rx="8" fill="url(#' . $id . 'gl)" opacity=".6"/>';
        }

        return '<g>'
            . '<path d="M110 176 h180 a16 16 0 0 1 16 16 v2 a16 16 0 0 0 -16 -16 h-180 a16 16 0 0 0 -16 16 v-2 a16 16 0 0 1 16 -16z" fill="' . self::NAVY . '" opacity=".2"/>'
            . '<rect x="94" y="78" width="212" height="100" rx="14" fill="' . self::NAVY . '"/>'
            . '<rect x="106" y="90" width="188" height="76" rx="8" fill="url(#' . $id . 'metal)" opacity=".9"/>'
            . '<rect x="94" y="176" width="212" height="152" rx="14" fill="#ffffff"/>'
            . '<rect x="94" y="176" width="212" height="152" rx="14" fill="none" stroke="' . self::NAVY . '" stroke-opacity=".15"/>'
            . $pans
            . '</g>';
    }

    private static function eyeliner(string $c, string $id): string
    {
        return '<g transform="rotate(-32 200 200)">'
            . '<rect x="178" y="70" width="44" height="200" rx="10" fill="' . self::NAVY . '"/>'
            . '<rect x="178" y="70" width="44" height="200" rx="10" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="184" y="90" width="32" height="150" rx="6" fill="' . $c . '" opacity=".25"/>'
            . '<rect x="182" y="266" width="36" height="22" rx="4" fill="url(#' . $id . 'gold)"/>'
            . '<path d="M190 288 L210 288 L200 336 Z" fill="' . $c . '"/>'
            . '<text x="200" y="180" font-family="Georgia, serif" font-size="11" letter-spacing="3" fill="#ffffff" opacity=".8" text-anchor="middle" transform="rotate(-90 200 180)">PLUME</text>'
            . '</g>'
            . '<path d="M70 300 c60 -40 120 -10 160 -30 c30 -15 50 -50 110 -60" fill="none" stroke="' . $c . '" stroke-width="5" stroke-linecap="round" opacity=".55"/>';
    }

    private static function brow(string $c, string $id): string
    {
        $bristles = '';
        for ($i = 0; $i < 9; $i++) {
            $y = 84 + $i * 7;
            $bristles .= '<path d="M200 ' . $y . ' h-14 M200 ' . $y . ' h14" stroke="' . self::NAVY . '" stroke-width="1.6" stroke-linecap="round" opacity=".6"/>';
        }

        return '<g>'
            . '<rect x="186" y="150" width="28" height="170" rx="8" fill="' . $c . '"/>'
            . '<rect x="186" y="150" width="28" height="170" rx="8" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="184" y="140" width="32" height="14" rx="3" fill="url(#' . $id . 'gold)"/>'
            . '<rect x="197" y="76" width="6" height="66" rx="3" fill="' . self::NAVY . '"/>'
            . $bristles
            . '<rect x="190" y="320" width="20" height="14" rx="3" fill="url(#' . $id . 'gold)"/>'
            . '<path d="M190 334 h20 l-8 22 h-4 z" fill="' . self::shade($c, -0.3) . '"/>'
            . '<path d="M90 230 c30 -30 70 -36 120 -22" fill="none" stroke="' . $c . '" stroke-width="6" stroke-linecap="round" opacity=".5"/>'
            . '</g>';
    }

    private static function foundation(string $c, string $id): string
    {
        return '<g>'
            . '<rect x="140" y="128" width="120" height="196" rx="22" fill="#ffffff" opacity=".75"/>'
            . '<rect x="148" y="150" width="104" height="166" rx="18" fill="' . $c . '"/>'
            . '<rect x="140" y="128" width="120" height="196" rx="22" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="140" y="128" width="120" height="196" rx="22" fill="none" stroke="' . self::NAVY . '" stroke-opacity=".12"/>'
            . '<rect x="178" y="98" width="44" height="34" rx="6" fill="url(#' . $id . 'metal)"/>'
            . '<rect x="190" y="68" width="20" height="34" rx="4" fill="url(#' . $id . 'metal)"/>'
            . '<path d="M210 76 h26 a6 6 0 0 1 0 12 h-26z" fill="url(#' . $id . 'metal)"/>'
            . '<rect x="158" y="214" width="84" height="54" rx="6" fill="#ffffff" opacity=".9"/>'
            . '<text x="200" y="238" font-family="Georgia, serif" font-size="14" letter-spacing="2" fill="' . self::NAVY . '" text-anchor="middle">%%BRAND%%</text>'
            . '<text x="200" y="256" font-family="Helvetica, Arial, sans-serif" font-size="8" letter-spacing="1" fill="' . self::NAVY . '" opacity=".7" text-anchor="middle">VOILE DE SOIE · SPF 20</text>'
            . '</g>';
    }

    private static function concealer(string $c, string $id): string
    {
        return '<g>'
            . '<rect x="166" y="160" width="68" height="160" rx="16" fill="' . $c . '"/>'
            . '<rect x="166" y="160" width="68" height="160" rx="16" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="172" y="110" width="56" height="54" rx="10" fill="' . self::NAVY . '"/>'
            . '<rect x="172" y="110" width="56" height="54" rx="10" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="176" y="230" width="48" height="40" rx="4" fill="#ffffff" opacity=".9"/>'
            . '<text x="200" y="254" font-family="Georgia, serif" font-size="11" letter-spacing="2" fill="' . self::NAVY . '" text-anchor="middle">ÉCLAT</text>'
            . '<path d="M262 120 c-10 50 -4 110 10 150" fill="none" stroke="' . self::shade($c, -0.15) . '" stroke-width="16" stroke-linecap="round" opacity=".55"/>'
            . '</g>';
    }

    private static function blush(string $c, string $id): string
    {
        return '<g>'
            . '<ellipse cx="200" cy="286" rx="92" ry="22" fill="' . self::NAVY . '" opacity=".06"/>'
            . '<path d="M112 200 a88 30 0 0 0 176 0 v70 a88 30 0 0 1 -176 0z" fill="#dbeafa" opacity=".9"/>'
            . '<path d="M112 200 a88 30 0 0 0 176 0 v70 a88 30 0 0 1 -176 0z" fill="url(#' . $id . 'gl)"/>'
            . '<ellipse cx="200" cy="200" rx="88" ry="30" fill="' . $c . '"/>'
            . '<ellipse cx="200" cy="200" rx="70" ry="22" fill="' . self::shade($c, 0.12) . '"/>'
            . '<path d="M160 196 q40 -14 80 0" fill="none" stroke="#ffffff" stroke-width="3" opacity=".6" stroke-linecap="round"/>'
            . '<g transform="translate(236 70) rotate(18)">'
            . '<rect x="-8" y="0" width="16" height="44" rx="4" fill="url(#' . $id . 'metal)"/>'
            . '<path d="M-44 44 h88 a44 44 0 0 1 -88 0z" fill="#dbeafa"/>'
            . '<path d="M-44 44 h88 a44 44 0 0 1 -88 0z" fill="url(#' . $id . 'gl)"/>'
            . '</g>'
            . '</g>';
    }

    private static function highlighter(string $c, string $id): string
    {
        return '<defs><radialGradient id="' . $id . 'hl" cx="40%" cy="35%" r="70%"><stop offset="0" stop-color="#ffffff"/><stop offset=".5" stop-color="' . $c . '"/><stop offset="1" stop-color="' . self::shade($c, -0.12) . '"/></radialGradient></defs>'
            . '<g>'
            . '<ellipse cx="200" cy="236" rx="110" ry="40" fill="' . self::NAVY . '"/>'
            . '<ellipse cx="200" cy="230" rx="110" ry="40" fill="url(#' . $id . 'metal)"/>'
            . '<ellipse cx="200" cy="230" rx="94" ry="30" fill="url(#' . $id . 'hl)"/>'
            . '<path d="M90 230 v-110 a110 40 0 0 1 220 0 v110" fill="none"/>'
            . '<ellipse cx="200" cy="110" rx="110" ry="40" fill="' . self::NAVY . '" opacity=".95"/>'
            . '<ellipse cx="200" cy="106" rx="110" ry="40" fill="url(#' . $id . 'metal)" opacity=".35"/>'
            . '<path d="M90 110 v10 a110 40 0 0 0 220 0 v-10 a110 40 0 0 1 -220 0z" fill="' . self::NAVY . '"/>'
            . '<text x="200" y="112" font-family="Georgia, serif" font-size="16" letter-spacing="4" fill="#ffffff" opacity=".85" text-anchor="middle">%%BRAND%%</text>'
            . '<path d="M160 212 l6 -14 6 14 -6 14z M250 240 l4 -9 4 9 -4 9z" fill="#ffffff" opacity=".8"/>'
            . '</g>';
    }

    private static function spray(string $c, string $id): string
    {
        return '<g>'
            . '<rect x="150" y="120" width="100" height="204" rx="20" fill="' . $c . '" opacity=".65"/>'
            . '<rect x="150" y="120" width="100" height="204" rx="20" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="150" y="120" width="100" height="204" rx="20" fill="none" stroke="' . self::NAVY . '" stroke-opacity=".12"/>'
            . '<rect x="172" y="88" width="56" height="36" rx="6" fill="url(#' . $id . 'metal)"/>'
            . '<rect x="186" y="64" width="28" height="28" rx="5" fill="' . self::NAVY . '"/>'
            . '<path d="M214 72 h10 v12 h-10z" fill="' . self::NAVY . '"/>'
            . '<rect x="166" y="200" width="68" height="60" rx="4" fill="#ffffff" opacity=".9"/>'
            . '<text x="200" y="226" font-family="Georgia, serif" font-size="13" letter-spacing="2" fill="' . self::NAVY . '" text-anchor="middle">MATIN</text>'
            . '<text x="200" y="244" font-family="Georgia, serif" font-size="13" letter-spacing="2" fill="' . self::NAVY . '" text-anchor="middle">FRAIS</text>'
            . '<g fill="' . self::NAVY . '" opacity=".35"><circle cx="252" cy="70" r="3"/><circle cx="268" cy="58" r="4"/><circle cx="286" cy="76" r="3"/><circle cx="278" cy="96" r="2.5"/><circle cx="300" cy="56" r="2.5"/></g>'
            . '</g>';
    }

    private static function nailPolish(string $c, string $id): string
    {
        return '<g>'
            . '<rect x="178" y="62" width="44" height="120" rx="10" fill="' . self::NAVY . '"/>'
            . '<rect x="178" y="62" width="44" height="120" rx="10" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="188" y="180" width="24" height="18" fill="url(#' . $id . 'metal)"/>'
            . '<path d="M146 220 a54 54 0 0 1 108 0 v70 a24 24 0 0 1 -24 24 h-60 a24 24 0 0 1 -24 -24z" fill="#ffffff" opacity=".7"/>'
            . '<path d="M152 236 a48 48 0 0 1 96 0 v54 a18 18 0 0 1 -18 18 h-60 a18 18 0 0 1 -18 -18z" fill="' . $c . '"/>'
            . '<path d="M146 220 a54 54 0 0 1 108 0 v70 a24 24 0 0 1 -24 24 h-60 a24 24 0 0 1 -24 -24z" fill="url(#' . $id . 'gl)"/>'
            . '<path d="M146 220 a54 54 0 0 1 108 0 v70 a24 24 0 0 1 -24 24 h-60 a24 24 0 0 1 -24 -24z" fill="none" stroke="' . self::NAVY . '" stroke-opacity=".14"/>'
            . '<path d="M164 232 q8 -22 30 -26" fill="none" stroke="#ffffff" stroke-width="4" stroke-linecap="round" opacity=".7"/>'
            . '<path d="M286 300 c-8 -40 10 -60 10 -60 s20 20 12 60a11 11 0 0 1 -22 0z" fill="' . $c . '"/>'
            . '</g>';
    }

    private static function brushes(string $c, string $id): string
    {
        $brush = static function (int $x, float $rot, int $w, int $tip, string $navy, string $id, string $handle): string {
            return '<g transform="translate(' . $x . ' 330) rotate(' . $rot . ')">'
                . '<rect x="' . (-$w / 2) . '" y="-150" width="' . $w . '" height="150" rx="' . ($w / 2) . '" fill="' . $handle . '"/>'
                . '<rect x="' . (-$w / 2) . '" y="-150" width="' . $w . '" height="150" rx="' . ($w / 2) . '" fill="url(#' . $id . 'gl)"/>'
                . '<rect x="' . (-$w / 2 - 1) . '" y="-180" width="' . ($w + 2) . '" height="32" rx="3" fill="url(#' . $id . 'metal)"/>'
                . '<path d="M' . (-$w / 2 - 2) . ' -180 q0 -' . $tip . ' ' . ($w / 2 + 2) . ' -' . ($tip + 14) . ' q' . ($w / 2 + 2) . ' ' . 14 . ' ' . ($w / 2 + 2) . ' ' . $tip . 'z" fill="' . $navy . '"/>'
                . '<path d="M' . (-$w / 2 + 2) . ' -182 q' . ($w / 4) . ' -' . ($tip - 4) . ' ' . ($w / 2 - 2) . ' -' . ($tip + 6) . '" fill="none" stroke="#ffffff" stroke-opacity=".35" stroke-width="2"/>'
                . '</g>';
        };

        return '<g>'
            . $brush(130, -14, 22, 40, self::shade($c, -0.55), $id, $c)
            . $brush(200, 0, 18, 56, self::shade($c, -0.55), $id, $c)
            . $brush(270, 14, 14, 36, self::shade($c, -0.55), $id, $c)
            . '</g>';
    }

    private static function pouch(string $c, string $id): string
    {
        $stitch = '';
        for ($i = 0; $i < 4; $i++) {
            $stitch .= '<path d="M' . (104 + $i * 52) . ' 150 v150" stroke="#ffffff" stroke-opacity=".45" stroke-width="2" stroke-dasharray="4 8"/>';
        }

        return '<g>'
            . '<path d="M84 160 h232 a16 16 0 0 1 16 16 v110 a30 30 0 0 1 -30 30 h-204 a30 30 0 0 1 -30 -30 v-110 a16 16 0 0 1 16 -16z" fill="' . $c . '"/>'
            . $stitch
            . '<path d="M84 160 h232 a16 16 0 0 1 16 16 v110 a30 30 0 0 1 -30 30 h-204 a30 30 0 0 1 -30 -30 v-110 a16 16 0 0 1 16 -16z" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="80" y="146" width="240" height="18" rx="6" fill="' . self::NAVY . '"/>'
            . '<rect x="96" y="151" width="208" height="3" fill="#ffffff" opacity=".35" stroke-dasharray="3 3"/>'
            . '<rect x="238" y="140" width="28" height="30" rx="5" fill="url(#' . $id . 'gold)"/>'
            . '<path d="M252 170 v26 a8 8 0 0 0 16 0" fill="none" stroke="url(#' . $id . 'gold)" stroke-width="5" stroke-linecap="round"/>'
            . '<text x="200" y="250" font-family="Georgia, serif" font-size="20" letter-spacing="6" fill="' . self::NAVY . '" opacity=".8" text-anchor="middle">%%BRAND%%</text>'
            . '</g>';
    }

    private static function giftBox(string $c, string $id): string
    {
        $lightBlue = '#dceefb';

        return '<g>'
            . '<rect x="96" y="176" width="208" height="150" rx="10" fill="' . $lightBlue . '"/>'
            . '<rect x="96" y="176" width="208" height="150" rx="10" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="80" y="138" width="240" height="52" rx="10" fill="#ffffff"/>'
            . '<rect x="80" y="138" width="240" height="52" rx="10" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="80" y="138" width="240" height="52" rx="10" fill="none" stroke="' . self::NAVY . '" stroke-opacity=".1"/>'
            . '<rect x="182" y="138" width="36" height="188" fill="' . $c . '"/>'
            . '<rect x="182" y="138" width="36" height="188" fill="url(#' . $id . 'gl)" opacity=".5"/>'
            . '<path d="M200 138 c-10 -30 -50 -50 -70 -32 c-14 14 10 32 70 32z" fill="' . $c . '"/>'
            . '<path d="M200 138 c10 -30 50 -50 70 -32 c14 14 -10 32 -70 32z" fill="' . self::shade($c, -0.12) . '"/>'
            . '<circle cx="200" cy="136" r="12" fill="' . self::shade($c, -0.2) . '"/>'
            . '<path d="M200 146 l-26 48 M200 146 l26 48" stroke="' . $c . '" stroke-width="10" stroke-linecap="round"/>'
            . '<text x="150" y="270" font-family="Georgia, serif" font-size="14" letter-spacing="4" fill="' . self::NAVY . '" opacity=".7" text-anchor="middle">%%BRAND%%</text>'
            . '</g>';
    }

    // ---- Montres -----------------------------------------------------------

    private static function watchRound(string $c, string $id, bool $jewel): string
    {
        $dialDark = self::luminance($c) < 0.5;
        $ink = $dialDark ? '#ffffff' : self::NAVY;
        $indices = '';
        for ($i = 0; $i < 12; $i++) {
            $angle = $i * 30;
            $long = $i % 3 === 0;
            $indices .= '<rect x="199" y="' . ($long ? 124 : 128) . '" width="' . ($long ? 3 : 2) . '" height="' . ($long ? 14 : 8) . '" rx="1" fill="' . $ink . '" opacity=".8" transform="rotate(' . $angle . ' 200 200)"/>';
        }
        $bezel = '';
        if ($jewel) {
            for ($i = 0; $i < 48; $i++) {
                $bezel .= '<circle cx="200" cy="112" r="3.2" fill="#ffffff" stroke="#c9b7ad" stroke-width=".6" transform="rotate(' . ($i * 7.5) . ' 200 200)"/>';
            }
        }
        $case = $jewel ? 'url(#' . $id . 'gold)' : 'url(#' . $id . 'metal)';
        $strap = $jewel ? self::GOLD : self::shade($c, -0.1);
        $strapTexture = $jewel
            ? '<g stroke="#ffffff" stroke-opacity=".5" stroke-width="2"><path d="M168 60 h64 M168 44 h64 M168 28 h64 M168 338 h64 M168 354 h64 M168 370 h64"/></g>'
            : '<g stroke="#ffffff" stroke-opacity=".35" stroke-width="1"><path d="M172 20 v90 M186 20 v90 M200 20 v90 M214 20 v90 M228 20 v90 M172 290 v100 M186 290 v100 M200 290 v100 M214 290 v100 M228 290 v100"/></g>';

        return '<g>'
            . '<rect x="166" y="0" width="68" height="120" fill="' . $strap . '"/>'
            . '<rect x="166" y="280" width="68" height="120" fill="' . $strap . '"/>'
            . $strapTexture
            . '<rect x="166" y="0" width="68" height="400" fill="url(#' . $id . 'gl)" opacity=".5"/>'
            . '<rect x="300" y="186" width="14" height="28" rx="4" fill="' . $case . '"/>'
            . '<circle cx="200" cy="200" r="102" fill="' . $case . '"/>'
            . '<circle cx="200" cy="200" r="88" fill="' . $c . '"/>'
            . '<circle cx="200" cy="200" r="88" fill="url(#' . $id . 'gl)" opacity=".7"/>'
            . $bezel
            . $indices
            . '<text x="200" y="160" font-family="Georgia, serif" font-size="11" letter-spacing="4" fill="' . $ink . '" opacity=".85" text-anchor="middle">%%BRAND%%</text>'
            . '<path d="M200 200 L164 176" stroke="' . $ink . '" stroke-width="5" stroke-linecap="round"/>'
            . '<path d="M200 200 L228 142" stroke="' . $ink . '" stroke-width="4" stroke-linecap="round"/>'
            . '<path d="M200 212 L252 250" stroke="#f2b9cc" stroke-width="2" stroke-linecap="round"/>'
            . '<circle cx="200" cy="200" r="5" fill="' . $ink . '"/>'
            . '<circle cx="200" cy="200" r="2" fill="#f2b9cc"/>'
            . '<circle cx="200" cy="200" r="102" fill="none" stroke="#ffffff" stroke-opacity=".6" stroke-width="2"/>'
            . '</g>';
    }

    private static function watchSquare(string $c, string $id): string
    {
        $numbers = '';
        foreach ([[200, 136, '12'], [262, 206, '3'], [200, 276, '6'], [138, 206, '9']] as [$x, $y, $n]) {
            $numbers .= '<text x="' . $x . '" y="' . $y . '" font-family="Georgia, serif" font-size="16" fill="' . self::NAVY . '" text-anchor="middle">' . $n . '</text>';
        }

        return '<g>'
            . '<rect x="160" y="0" width="80" height="400" fill="#a9c6e4"/>'
            . '<g stroke="#ffffff" stroke-opacity=".5" stroke-width="1.2" stroke-dasharray="6 5"><path d="M168 10 v100 M232 10 v100 M168 290 v100 M232 290 v100"/></g>'
            . '<rect x="160" y="0" width="80" height="400" fill="url(#' . $id . 'gl)" opacity=".5"/>'
            . '<rect x="290" y="186" width="14" height="28" rx="4" fill="url(#' . $id . 'metal)"/>'
            . '<rect x="104" y="104" width="192" height="192" rx="34" fill="url(#' . $id . 'metal)"/>'
            . '<rect x="120" y="120" width="160" height="160" rx="24" fill="' . $c . '"/>'
            . '<rect x="120" y="120" width="160" height="160" rx="24" fill="url(#' . $id . 'gl)" opacity=".5"/>'
            . $numbers
            . '<text x="200" y="172" font-family="Georgia, serif" font-size="9" letter-spacing="3" fill="' . self::NAVY . '" opacity=".8" text-anchor="middle">%%BRAND%%</text>'
            . '<path d="M200 200 L170 182" stroke="' . self::NAVY . '" stroke-width="4.5" stroke-linecap="round"/>'
            . '<path d="M200 200 L222 150" stroke="' . self::NAVY . '" stroke-width="3.5" stroke-linecap="round"/>'
            . '<path d="M200 208 L236 244" stroke="#f2b9cc" stroke-width="2" stroke-linecap="round"/>'
            . '<circle cx="200" cy="200" r="4.5" fill="' . self::NAVY . '"/>'
            . '</g>';
    }

    private static function watchMini(string $c, string $id): string
    {
        return '<g>'
            . '<path d="M60 230 a140 140 0 0 1 280 0" fill="none" stroke="url(#' . $id . 'gold)" stroke-width="14" stroke-linecap="round"/>'
            . '<path d="M60 230 a140 140 0 0 0 42 100 M340 230 a140 140 0 0 1 -42 100" fill="none" stroke="url(#' . $id . 'gold)" stroke-width="14" stroke-linecap="round"/>'
            . '<circle cx="102" cy="332" r="10" fill="url(#' . $id . 'gold)"/>'
            . '<circle cx="298" cy="332" r="10" fill="url(#' . $id . 'gold)"/>'
            . '<circle cx="200" cy="96" r="60" fill="url(#' . $id . 'gold)"/>'
            . '<circle cx="200" cy="96" r="50" fill="' . $c . '"/>'
            . '<circle cx="200" cy="96" r="50" fill="url(#' . $id . 'gl)" opacity=".6"/>'
            . '<g fill="' . self::NAVY . '" opacity=".75"><rect x="199" y="52" width="2" height="8"/><rect x="199" y="132" width="2" height="8"/><rect x="156" y="95" width="8" height="2"/><rect x="236" y="95" width="8" height="2"/></g>'
            . '<path d="M200 96 L182 84" stroke="' . self::NAVY . '" stroke-width="3.5" stroke-linecap="round"/>'
            . '<path d="M200 96 L214 66" stroke="' . self::NAVY . '" stroke-width="2.8" stroke-linecap="round"/>'
            . '<circle cx="200" cy="96" r="3" fill="' . self::NAVY . '"/>'
            . '<rect x="258" y="88" width="8" height="16" rx="3" fill="url(#' . $id . 'gold)"/>'
            . '</g>';
    }

    private static function watchPearl(string $c, string $id): string
    {
        $pearls = '';
        for ($i = 0; $i < 22; $i++) {
            $angle = deg2rad(100 + $i * 7.3);
            $x = 200 + cos($angle) * 150;
            $y = 180 + sin($angle) * 150;
            $pearls .= '<circle cx="' . round($x, 1) . '" cy="' . round($y, 1) . '" r="11" fill="#fbf4f6" stroke="#e7d4da" stroke-width="1"/>'
                . '<circle cx="' . round($x - 3, 1) . '" cy="' . round($y - 3, 1) . '" r="3" fill="#ffffff"/>';
        }

        return '<g>'
            . '<path d="M60 206 a150 150 0 0 1 280 0" fill="none" stroke="#e7d4da" stroke-width="2"/>'
            . $pearls
            . '<circle cx="200" cy="100" r="54" fill="url(#' . $id . 'gold)"/>'
            . '<circle cx="200" cy="100" r="44" fill="' . $c . '"/>'
            . '<circle cx="200" cy="100" r="44" fill="url(#' . $id . 'gl)" opacity=".5"/>'
            . '<text x="200" y="84" font-family="Georgia, serif" font-size="8" letter-spacing="3" fill="' . self::NAVY . '" opacity=".8" text-anchor="middle">%%BRAND%%</text>'
            . '<path d="M200 100 L184 90" stroke="' . self::NAVY . '" stroke-width="3" stroke-linecap="round"/>'
            . '<path d="M200 100 L212 74" stroke="' . self::NAVY . '" stroke-width="2.5" stroke-linecap="round"/>'
            . '<circle cx="200" cy="100" r="2.5" fill="' . self::NAVY . '"/>'
            . '<rect x="252" y="92" width="8" height="16" rx="3" fill="url(#' . $id . 'gold)"/>'
            . '</g>';
    }

    private static function watchChrono(string $c, string $id): string
    {
        $sub = static function (int $x, int $y, string $navy): string {
            return '<circle cx="' . $x . '" cy="' . $y . '" r="18" fill="#eef3f9" stroke="' . $navy . '" stroke-opacity=".25"/>'
                . '<path d="M' . $x . ' ' . $y . ' L' . ($x + 8) . ' ' . ($y - 10) . '" stroke="' . $navy . '" stroke-width="1.5" stroke-linecap="round"/>';
        };
        $ticks = '';
        for ($i = 0; $i < 60; $i++) {
            $ticks .= '<rect x="199.4" y="104" width="' . ($i % 5 === 0 ? 2 : 1) . '" height="' . ($i % 5 === 0 ? 10 : 5) . '" fill="' . self::NAVY . '" opacity=".7" transform="rotate(' . ($i * 6) . ' 200 200)"/>';
        }

        return '<g>'
            . '<rect x="160" y="0" width="80" height="400" rx="0" fill="' . self::shade($c, -0.08) . '"/>'
            . '<g fill="' . self::NAVY . '" opacity=".12"><circle cx="200" cy="30" r="4"/><circle cx="200" cy="54" r="4"/><circle cx="200" cy="78" r="4"/><circle cx="200" cy="322" r="4"/><circle cx="200" cy="346" r="4"/><circle cx="200" cy="370" r="4"/></g>'
            . '<rect x="160" y="0" width="80" height="400" fill="url(#' . $id . 'gl)" opacity=".4"/>'
            . '<rect x="304" y="150" width="14" height="22" rx="4" fill="url(#' . $id . 'metal)"/>'
            . '<rect x="304" y="228" width="14" height="22" rx="4" fill="url(#' . $id . 'metal)"/>'
            . '<rect x="306" y="188" width="14" height="24" rx="4" fill="url(#' . $id . 'metal)"/>'
            . '<circle cx="200" cy="200" r="112" fill="url(#' . $id . 'metal)"/>'
            . '<circle cx="200" cy="200" r="100" fill="' . self::NAVY . '"/>'
            . '<circle cx="200" cy="200" r="96" fill="' . $c . '"/>'
            . $ticks
            . $sub(200, 236, self::NAVY) . $sub(164, 190, self::NAVY) . $sub(236, 190, self::NAVY)
            . '<text x="200" y="156" font-family="Georgia, serif" font-size="10" letter-spacing="4" fill="' . self::NAVY . '" opacity=".85" text-anchor="middle">%%BRAND%%</text>'
            . '<path d="M200 200 L160 178" stroke="' . self::NAVY . '" stroke-width="5" stroke-linecap="round"/>'
            . '<path d="M200 200 L232 146" stroke="' . self::NAVY . '" stroke-width="4" stroke-linecap="round"/>'
            . '<path d="M200 214 L200 118" stroke="#f08aa6" stroke-width="2" stroke-linecap="round"/>'
            . '<circle cx="200" cy="200" r="5" fill="' . self::NAVY . '"/>'
            . '</g>';
    }

    private static function strap(string $c, string $id): string
    {
        $holes = '';
        for ($i = 0; $i < 6; $i++) {
            $holes .= '<circle cx="' . (236 + $i * 16) . '" cy="' . (236 - $i * 9) . '" r="4" fill="' . self::shade($c, -0.35) . '"/>';
        }

        return '<g>'
            . '<path d="M70 300 C 110 180, 200 160, 330 150" fill="none" stroke="' . self::shade($c, -0.1) . '" stroke-width="44" stroke-linecap="round"/>'
            . '<path d="M70 300 C 110 180, 200 160, 330 150" fill="none" stroke="' . $c . '" stroke-width="36" stroke-linecap="round"/>'
            . '<path d="M70 300 C 110 180, 200 160, 330 150" fill="none" stroke="#ffffff" stroke-opacity=".5" stroke-width="1.5" stroke-dasharray="5 6" transform="translate(0 -12)"/>'
            . '<path d="M70 300 C 110 180, 200 160, 330 150" fill="none" stroke="#ffffff" stroke-opacity=".5" stroke-width="1.5" stroke-dasharray="5 6" transform="translate(0 12)"/>'
            . '<circle cx="236" cy="236" r="4" fill="' . self::shade($c, -0.35) . '"/>'
            . '<circle cx="252" cy="227" r="4" fill="' . self::shade($c, -0.35) . '"/>'
            . '<circle cx="268" cy="218" r="4" fill="' . self::shade($c, -0.35) . '"/>'
            . '<circle cx="284" cy="210" r="4" fill="' . self::shade($c, -0.35) . '"/>'
            . '<rect x="52" y="266" width="40" height="60" rx="8" fill="url(#' . $id . 'metal)" transform="rotate(-62 72 296)"/>'
            . '<rect x="64" y="276" width="16" height="40" rx="4" fill="' . $c . '" transform="rotate(-62 72 296)"/>'
            . '<path d="M72 296 l20 -34" stroke="url(#' . $id . 'metal)" stroke-width="4" stroke-linecap="round"/>'
            . '</g>';
    }

    private static function travelCase(string $c, string $id): string
    {
        $cushions = '';
        foreach ([128, 200, 272] as $x) {
            $cushions .= '<rect x="' . ($x - 28) . '" y="214" width="56" height="78" rx="20" fill="#f6dfe6"/>'
                . '<rect x="' . ($x - 28) . '" y="214" width="56" height="78" rx="20" fill="url(#' . $id . 'gl)"/>';
        }

        return '<g>'
            . '<rect x="80" y="196" width="240" height="118" rx="14" fill="' . $c . '"/>'
            . '<rect x="92" y="206" width="216" height="98" rx="10" fill="#f9e9ee"/>'
            . $cushions
            . '<path d="M80 196 h240 v-18 a14 14 0 0 0 -14 -14 h-212 a14 14 0 0 0 -14 14z" fill="' . self::shade($c, -0.08) . '"/>'
            . '<path d="M86 164 h228 a12 12 0 0 1 12 12 v4 h-252 v-4 a12 12 0 0 1 12 -12z" fill="' . $c . '"/>'
            . '<path d="M86 164 l6 -70 a10 10 0 0 1 10 -8 h196 a10 10 0 0 1 10 8 l6 70z" fill="' . self::shade($c, 0.08) . '"/>'
            . '<path d="M86 164 l6 -70 a10 10 0 0 1 10 -8 h196 a10 10 0 0 1 10 8 l6 70z" fill="url(#' . $id . 'gl)"/>'
            . '<rect x="182" y="304" width="36" height="12" rx="4" fill="url(#' . $id . 'gold)"/>'
            . '<text x="200" y="134" font-family="Georgia, serif" font-size="16" letter-spacing="5" fill="' . self::NAVY . '" opacity=".75" text-anchor="middle">%%BRAND%%</text>'
            . '</g>';
    }

    // ---- Utilitaires couleurs --------------------------------------------

    /**
     * Éclaircit (facteur > 0) ou assombrit (facteur < 0) une couleur hexadécimale.
     */
    private static function shade(string $hex, float $factor): string
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x') ?: [0, 0, 0];
        $mix = static function (int $channel) use ($factor): int {
            $target = $factor > 0 ? 255 : 0;

            return (int) max(0, min(255, round($channel + ($target - $channel) * abs($factor))));
        };

        return sprintf('#%02x%02x%02x', $mix((int) $r), $mix((int) $g), $mix((int) $b));
    }

    private static function luminance(string $hex): float
    {
        [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x') ?: [255, 255, 255];

        return (0.299 * (int) $r + 0.587 * (int) $g + 0.114 * (int) $b) / 255;
    }
}
