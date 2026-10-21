<?php

declare(strict_types=1);

namespace Daris\Payment;

use Daris\Exception\PaymentException;

/**
 * Point d'extension pour un prestataire de paiement (Stripe, Datatrans,
 * Payrexx, TWINT…). La boutique ne manipule jamais de données de carte :
 * le prestataire renvoie une référence de transaction, c'est tout.
 */
interface PaymentGatewayInterface
{
    /**
     * Autorise (ou enregistre) le paiement d'une commande.
     *
     * @param int    $amountCents Montant total TTC en centimes
     * @param string $method      Moyen choisi : invoice, twint, card…
     * @param string $reference   Référence de commande
     *
     * @throws PaymentException si le paiement est refusé ou indisponible
     */
    public function authorize(int $amountCents, string $method, string $reference): PaymentResult;
}
