<?php

declare(strict_types=1);

namespace Daris\Payment;

use Daris\Exception\PaymentException;

/**
 * Passerelle de démonstration : accepte tout paiement et renvoie une
 * référence fictive. À remplacer par une implémentation réelle (voir
 * PaymentGatewayInterface) en changeant PAYMENT_PROVIDER dans .env.
 */
final class SimulatedGateway implements PaymentGatewayInterface
{
    private const LABELS = [
        'invoice' => 'Facture à régler sous 30 jours',
        'twint' => 'Paiement TWINT simulé',
        'card' => 'Paiement par carte simulé',
    ];

    public function authorize(int $amountCents, string $method, string $reference): PaymentResult
    {
        if ($amountCents <= 0) {
            throw new PaymentException('Le montant à payer est invalide.');
        }
        if (!isset(self::LABELS[$method])) {
            throw new PaymentException('Moyen de paiement inconnu.');
        }

        return new PaymentResult('SIM-' . strtoupper(substr(hash('sha256', $reference . $method), 0, 10)), self::LABELS[$method], true);
    }
}
