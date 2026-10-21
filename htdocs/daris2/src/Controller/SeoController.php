<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Http\Response;

/**
 * sitemap.xml et robots.txt générés dynamiquement depuis le catalogue.
 */
final class SeoController extends AbstractController
{
    private const STATIC_PATHS = [
        '/' => ['weekly', '1.0'],
        '/boutique' => ['weekly', '0.9'],
        '/tarifs' => ['monthly', '0.8'],
        '/conseils' => ['monthly', '0.7'],
        '/guide' => ['monthly', '0.7'],
        '/exemples' => ['monthly', '0.7'],
        '/inspirations' => ['monthly', '0.7'],
        '/chat' => ['monthly', '0.5'],
        '/conseillere' => ['monthly', '0.5'],
        '/a-propos' => ['yearly', '0.5'],
        '/contact' => ['yearly', '0.5'],
        '/commande/suivi' => ['yearly', '0.3'],
        '/confidentialite' => ['yearly', '0.2'],
        '/cgv' => ['yearly', '0.2'],
    ];

    public function sitemap(): Response
    {
        $url = $this->services->url();
        $catalog = $this->services->catalog();
        $today = gmdate('Y-m-d');
        $entries = [];

        foreach (self::STATIC_PATHS as $path => [$freq, $priority]) {
            $entries[] = [$url->absolute($path), $today, $freq, $priority];
        }
        foreach ($catalog->categories() as $category) {
            $entries[] = [$url->absolute('/boutique/' . $category->slug()), $today, 'weekly', '0.8'];
        }
        foreach ($catalog->all() as $product) {
            $entries[] = [$url->absolute('/produit/' . $product->slug()), $today, 'weekly', '0.7'];
        }
        foreach ($this->services->looks()->all() as $look) {
            $entries[] = [$url->absolute('/exemples/' . $look->slug()), $today, 'monthly', '0.6'];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($entries as [$loc, $lastmod, $freq, $priority]) {
            $xml .= sprintf(
                "  <url><loc>%s</loc><lastmod>%s</lastmod><changefreq>%s</changefreq><priority>%s</priority></url>\n",
                htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8'),
                $lastmod,
                $freq,
                $priority
            );
        }
        $xml .= '</urlset>' . "\n";

        return Response::text($xml, 'application/xml; charset=utf-8')
            ->withHeader('Cache-Control', 'public, max-age=3600');
    }

    public function robots(): Response
    {
        $url = $this->services->url();
        $base = $url->basePath() . '/';

        $lines = [
            'User-agent: *',
            'Allow: ' . $base,
            'Disallow: ' . $base . 'panier',
            'Disallow: ' . $base . 'commande',
            'Disallow: ' . $base . 'conseillere/repondre',
            'Disallow: ' . $base . 'chat/repondre',
            'Disallow: ' . $base . 'newsletter',
            'Disallow: ' . $base . 'recherche',
            'Disallow: ' . $base . 'boutique?*q=',
            'Disallow: ' . $base . 'src/',
            'Disallow: ' . $base . 'templates/',
            'Disallow: ' . $base . 'config/',
            'Disallow: ' . $base . 'var/',
            'Disallow: ' . $base . 'data/',
            '',
            'Sitemap: ' . $url->absolute('/sitemap.xml'),
        ];

        return Response::text(implode("\n", $lines) . "\n")
            ->withHeader('Cache-Control', 'public, max-age=86400');
    }
}
