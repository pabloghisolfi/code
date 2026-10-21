<?php

declare(strict_types=1);

namespace Daris\Exception;

/**
 * Page, produit ou ressource introuvable (404).
 */
final class NotFoundException extends HttpException
{
    public function __construct(string $message = 'Page introuvable.')
    {
        parent::__construct(404, $message);
    }
}
