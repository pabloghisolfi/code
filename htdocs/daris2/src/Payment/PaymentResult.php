<?php

declare(strict_types=1);

namespace Daris\Payment;

/**
 * Résultat d'une autorisation de paiement.
 */
final class PaymentResult
{
    public function __construct(
        private readonly string $transactionReference,
        private readonly string $statusLabel,
        private readonly bool $simulated
    ) {
    }

    public function transactionReference(): string
    {
        return $this->transactionReference;
    }

    public function statusLabel(): string
    {
        return $this->statusLabel;
    }

    public function isSimulated(): bool
    {
        return $this->simulated;
    }
}
