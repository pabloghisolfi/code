<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Http\Response;
use Throwable;

/**
 * Pages d'erreur (404, 405, 419, 429, 500) dans l'habillage du site.
 */
final class ErrorController extends AbstractController
{
    private const TITLES = [
        404 => 'Cette page n\'existe pas',
        405 => 'Méthode non autorisée',
        419 => 'Votre session a expiré',
        429 => 'Un instant…',
        500 => 'Un petit souci de notre côté',
    ];

    private const LEADS = [
        404 => 'L\'adresse est peut-être incomplète, ou le produit n\'est plus proposé. Le reste de la boutique est bien là.',
        405 => 'Cette adresse n\'accepte pas ce type de requête.',
        419 => 'Par sécurité, les formulaires ne restent valables qu\'un certain temps. Revenez en arrière et réessayez.',
        429 => 'Vous avez envoyé beaucoup de demandes en peu de temps. Patientez quelques minutes avant de réessayer.',
        500 => 'Une erreur est survenue. Nos équipes en sont informées ; réessayez dans un instant.',
    ];

    public function render(int $status, Throwable $exception, bool $debug): Response
    {
        $status = isset(self::TITLES[$status]) ? $status : ($status >= 500 ? 500 : 404);

        if ($this->wantsJson()) {
            return Response::json(['ok' => false, 'error' => self::TITLES[$status]], $status);
        }

        return $this->page('error', [
            'status' => $status,
            'heading' => self::TITLES[$status],
            'lead' => $exception->getMessage() !== '' && $status !== 500 ? $exception->getMessage() : self::LEADS[$status],
            'debug' => $debug && $status >= 500 ? $exception::class . ': ' . $exception->getMessage() . "\n" . $exception->getTraceAsString() : '',
            'suggestions' => $this->services->catalog()->featured(4),
        ], [
            'title' => self::TITLES[$status] . ' – Daris',
            'description' => self::LEADS[$status],
            'noindex' => true,
            'bodyClass' => 'page-error',
        ], $status);
    }
}
