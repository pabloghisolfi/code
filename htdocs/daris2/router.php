<?php

/**
 * Routeur pour le serveur de développement intégré de PHP.
 *
 * Reproduit le comportement du .htaccess (Apache) :
 *   php -S localhost:8080 -t htdocs htdocs/daris2/router.php
 * puis ouvrir http://localhost:8080/daris2/
 */

declare(strict_types=1);

$uri = (string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
$docRoot = rtrim((string) ($_SERVER['DOCUMENT_ROOT'] ?? dirname(__DIR__)), '/');
$prefix = '/' . basename(__DIR__);

// Dossiers internes : interdits comme sous Apache.
if (preg_match('#^' . preg_quote($prefix, '#') . '/(src|templates|config|var|data|vendor)(/|$)#', $uri) === 1) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "Accès refusé.\n";

    return true;
}

// Fichiers réels (assets) : laissés au serveur intégré.
$file = $docRoot . $uri;
if ($uri !== $prefix . '/' && is_file($file) && !str_ends_with($file, '.php')) {
    return false;
}

$_SERVER['SCRIPT_NAME'] = $prefix . '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/index.php';

require __DIR__ . '/index.php';

return true;
