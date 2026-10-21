<?php

declare(strict_types=1);

namespace Daris\Exception;

use Throwable;

/**
 * Erreur HTTP à présenter au visiteur avec un code de statut précis.
 */
class HttpException extends DarisException
{
    public function __construct(
        private readonly int $statusCode,
        string $message = '',
        ?Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}
