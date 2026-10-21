<?php

/**
 * Valeurs par défaut lorsque le layout est rendu hors cycle normal (page d'erreur).
 *
 * @var array<string, string|int>|null $app
 * @var list<\Daris\Catalog\Category>|null $navCategories
 * @var int|null $cartCount
 * @var string|null $currentPath
 */
if (!isset($app) || !is_array($app)) {
    $app = [
        'name' => 'Azurine',
        'tagline' => 'Maquillage & montres',
        'credit' => 'Site codé par Daris Jamini',
        'themeColor' => '#dceefb',
        'email' => 'bonjour@azurine.ch',
        'phone' => '+41 21 000 00 00',
        'address' => 'Rue du Lac 12, 1003 Lausanne',
        'freeShippingFrom' => 8000,
    ];
}

$navCategories ??= [];
$cartCount ??= 0;
$currentPath ??= '';
