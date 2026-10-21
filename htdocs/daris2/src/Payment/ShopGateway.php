<?php

declare(strict_types=1);

namespace Daris\Payment;

use Daris\Config\Config;
use Daris\Exception\PaymentException;
use Daris\Support\Url;

/**
 * Paiements boutique : carte via Stripe si configuré, sinon commande enregistrée
 * sans simulation de charge. Facture et TWINT enregistrés hors ligne.
 */
final class ShopGateway implements PaymentGatewayInterface
{
    public function __construct(
        private readonly Config $config,
        private readonly Url $url
    ) {
    }

    public function authorize(int $amountCents, string $method, string $reference, string $orderToken = ''): PaymentResult
    {
        if ($amountCents <= 0) {
            throw new PaymentException('Le montant à payer est invalide.');
        }

        $methods = $this->config->array('shop.payment_methods');
        if (!isset($methods[$method])) {
            throw new PaymentException('Moyen de paiement inconnu.');
        }

        if ($method === 'card') {
            $secret = trim($this->config->string('payment.stripe_secret', ''));
            if ($secret === '') {
                return new PaymentResult(
                    'PENDING-CARD',
                    'Carte : module Stripe non configuré — commande enregistrée, nous vous contacterons pour le règlement.',
                    false
                );
            }

            $redirect = $this->createStripeCheckoutSession($secret, $amountCents, $reference, $orderToken);
            if ($redirect === null) {
                throw new PaymentException('Impossible d\'ouvrir la page de paiement Stripe. Réessayez ou choisissez un autre moyen.');
            }

            return new PaymentResult('STRIPE-SESSION', 'Paiement carte via Stripe', false, $redirect);
        }

        $label = is_array($methods[$method]) ? (string) ($methods[$method]['label'] ?? $method) : $method;

        return new PaymentResult(
            'MANUAL-' . strtoupper($method),
            $label . ' — commande enregistrée',
            false
        );
    }

    private function createStripeCheckoutSession(string $secret, int $amountCents, string $reference, string $orderToken): ?string
    {
        $success = $this->url->absolute('/commande/' . $reference);
        if ($orderToken !== '') {
            $success .= '?t=' . rawurlencode($orderToken);
        }
        $cancel = $this->url->absolute('/commande') . '#paiement';

        $body = http_build_query([
            'mode' => 'payment',
            'success_url' => $success,
            'cancel_url' => $cancel,
            'client_reference_id' => $reference,
            'line_items[0][quantity]' => 1,
            'line_items[0][price_data][currency]' => 'chf',
            'line_items[0][price_data][unit_amount]' => $amountCents,
            'line_items[0][price_data][product_data][name]' => 'Commande Azurine ' . $reference,
        ], '', '&', PHP_QUERY_RFC3986);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Authorization: Bearer {$secret}\r\nContent-Type: application/x-www-form-urlencoded\r\n",
                'content' => $body,
                'timeout' => 15,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents('https://api.stripe.com/v1/checkout/sessions', false, $context);
        if ($raw === false) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || !isset($decoded['url']) || !is_string($decoded['url'])) {
            return null;
        }

        return $decoded['url'];
    }
}
