<?php

declare(strict_types=1);

namespace Daris\Exception;

/**
 * Données de formulaire invalides, avec la liste des erreurs par champ.
 */
final class ValidationException extends DarisException
{
    /**
     * @param array<string, string> $errors
     */
    public function __construct(private readonly array $errors, string $message = 'Le formulaire contient des erreurs.')
    {
        parent::__construct($message);
    }

    /**
     * @return array<string, string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
