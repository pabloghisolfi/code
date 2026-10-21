<?php

declare(strict_types=1);

namespace Daris\Http;

/**
 * En-têtes de sécurité appliqués à toutes les réponses dynamiques.
 *
 * Note HSTS : l'en-tête Strict-Transport-Security n'est émis qu'en HTTPS,
 * sinon un site de test en HTTP pourrait devenir inaccessible. Une fois le
 * certificat en place et vérifié, on peut augmenter max-age et ajouter
 * « preload ».
 */
final class SecurityHeaders
{
    private const CSP = "default-src 'self'; "
        . "script-src 'self'; "
        . "style-src 'self' https://fonts.googleapis.com; "
        . "font-src 'self' https://fonts.gstatic.com; "
        . "img-src 'self' data:; "
        . "connect-src 'self'; "
        . "form-action 'self'; "
        . "frame-ancestors 'none'; "
        . "base-uri 'self'; "
        . "object-src 'none'; "
        . "upgrade-insecure-requests";

    public static function apply(Response $response, bool $https): Response
    {
        $csp = $https ? self::CSP : str_replace('; upgrade-insecure-requests', '', self::CSP);

        $response = $response
            ->withHeader('Content-Security-Policy', $csp)
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->withHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), interest-cohort=()')
            ->withHeader('Cross-Origin-Opener-Policy', 'same-origin')
            ->withHeader('Cross-Origin-Resource-Policy', 'same-origin')
            ->withHeader('X-Permitted-Cross-Domain-Policies', 'none');

        if ($https) {
            $response = $response->withHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
