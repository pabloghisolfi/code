<?php

declare(strict_types=1);

namespace Daris\Support;

/**
 * Génération d'URL tenant compte du préfixe de dossier (/daris2).
 */
final class Url
{
    public function __construct(
        private readonly string $basePath,
        private readonly string $siteUrl,
        private readonly string $assetsDir
    ) {
    }

    public function basePath(): string
    {
        return $this->basePath;
    }

    /**
     * Chemin absolu (côté serveur) vers une route : to('/produit/lune') → /daris2/produit/lune
     *
     * @param array<string, scalar|list<string>> $query
     */
    public function to(string $path = '/', array $query = []): string
    {
        $path = '/' . ltrim($path, '/');
        $url = ($this->basePath === '' ? '' : $this->basePath) . $path;
        if ($path === '/' && $this->basePath !== '') {
            $url = $this->basePath . '/';
        }

        if ($query !== []) {
            $url .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        }

        return $url;
    }

    /**
     * URL absolue (https://hôte/daris2/...), pour le sitemap, canonical et e-mails.
     */
    public function absolute(string $path = '/'): string
    {
        return rtrim($this->siteUrl, '/') . $this->to($path);
    }

    /**
     * URL d'un fichier du dossier assets, suffixée par sa date de modification
     * pour invalider les caches navigateur à chaque mise à jour.
     */
    public function asset(string $file): string
    {
        $file = ltrim($file, '/');
        $real = $this->assetsDir . '/' . $file;
        $stamp = is_file($real) ? (filemtime($real) ?: 0) : 0;

        return $this->to('/assets/' . $file) . ($stamp > 0 ? '?v=' . $stamp : '');
    }

    /**
     * Préfixe public du dossier assets (sans version), utile pour les chemins SVG.
     */
    public function assetsPath(): string
    {
        return $this->to('/assets');
    }
}
