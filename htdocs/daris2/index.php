<?php

/**
 * Contrôleur frontal de la boutique Daris.
 *
 * Toutes les requêtes dynamiques arrivent ici (réécriture Apache ou router.php
 * en développement). Le dossier est prévu pour être déposé tel quel dans
 * htdocs : http://hôte/daris2/
 */

declare(strict_types=1);

use Daris\Kernel\Application;

$autoload = __DIR__ . '/vendor/autoload.php';

if (is_file($autoload)) {
    require $autoload;
} else {
    // Autoloader PSR-4 de secours lorsque « composer install » n'a pas été lancé.
    spl_autoload_register(static function (string $class): void {
        $prefix = 'Daris\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $file = __DIR__ . '/src/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    });
}

$config = require __DIR__ . '/config/app.php';

(new Application($config))->run();
