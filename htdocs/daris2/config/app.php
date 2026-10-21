<?php

/**
 * Configuration de l'application.
 *
 * Les valeurs sensibles ou dépendantes de l'hébergement viennent du fichier
 * .env (voir .env.example). Rien de secret n'est écrit ici.
 */

declare(strict_types=1);

use Daris\Config\Env;

$root = dirname(__DIR__);
Env::load($root . '/.env');

return [
    'app' => [
        'name' => 'Azurine',
        'tagline' => 'Maquillage & montres',
        'env' => Env::get('APP_ENV', 'production'),
        'debug' => Env::bool('APP_DEBUG', false),
        'base_path' => rtrim(Env::get('APP_BASE_PATH', '/daris2'), '/'),
        'url' => Env::get('APP_URL', ''),
        'secret' => Env::get('APP_SECRET', ''),
        'locale' => 'fr_CH',
        'theme_color' => '#5a9fd4',
        'credit' => 'Site codé par Daris Jamini',
    ],
    'paths' => [
        'root' => $root,
        'data' => $root . '/data',
        'templates' => $root . '/templates',
        'var' => $root . '/var',
        'assets' => $root . '/assets',
    ],
    'shop' => [
        'email' => Env::get('SHOP_EMAIL', 'bonjour@azurine.ch'),
        'phone' => Env::get('SHOP_PHONE', '+41 21 000 00 00'),
        'address' => 'Rue du Lac 12, 1003 Lausanne',
        'currency' => 'CHF',
        'vat_rate' => 8.1,
        'free_shipping_from' => 8000,
        'gift_wrap_price' => 490,
        'shipping_methods' => [
            'economy' => ['label' => 'PostPac Economy', 'delay' => '2 à 3 jours ouvrés', 'price' => 700],
            'priority' => ['label' => 'PostPac Priority', 'delay' => 'lendemain ouvré', 'price' => 900],
        ],
        'payment_methods' => [
            'invoice' => ['label' => 'Facture (30 jours)', 'hint' => 'Payez après réception, par QR-facture.'],
            'twint' => ['label' => 'TWINT', 'hint' => 'Paiement mobile suisse.'],
            'card' => ['label' => 'Carte de crédit', 'hint' => 'Visa, Mastercard, American Express.'],
        ],
        'promo_codes' => [
            'BIENVENUE10' => ['type' => 'percent', 'value' => 10, 'label' => '-10 % de bienvenue'],
            'LIVRAISON' => ['type' => 'shipping', 'value' => 0, 'label' => 'Livraison offerte'],
        ],
    ],
    'mail' => [
        'enabled' => Env::bool('MAIL_ENABLED', false),
        'from' => Env::get('MAIL_FROM', 'Azurine <no-reply@azurine.ch>'),
    ],
    'database' => [
        'file' => Env::get('DB_FILE', 'daris.sqlite'),
    ],
    'payment' => [
        'provider' => Env::get('PAYMENT_PROVIDER', 'none'),
    ],
    'security' => [
        'session_name' => 'daris_session',
        'session_lifetime' => 60 * 60 * 4,
        'rate_limit_attempts' => Env::int('RATE_LIMIT_ATTEMPTS', 5),
        'rate_limit_window' => Env::int('RATE_LIMIT_WINDOW', 600),
    ],
];
