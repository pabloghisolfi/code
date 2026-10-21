<?php

declare(strict_types=1);

namespace Daris\Payment;

use Daris\Exception\ConfigurationException;

/**
 * Choisit la passerelle de paiement selon PAYMENT_PROVIDER.
 */
final class GatewayFactory
{
    private function __construct()
    {
    }

    public static function make(string $provider): PaymentGatewayInterface
    {
        return match ($provider) {
            'none', '', 'simulated' => new SimulatedGateway(),
            default => throw new ConfigurationException(sprintf('Prestataire de paiement non pris en charge : %s', $provider)),
        };
    }
}
