<?php

declare(strict_types=1);

namespace Daris\Order;

use Daris\Cart\Cart;
use Daris\Cart\CartItem;
use Daris\Config\Config;
use Daris\Mail\Mailer;
use Daris\Payment\PaymentGatewayInterface;
use Daris\Support\Url;
use Daris\View\View;

/**
 * Orchestration d'une commande : montants, paiement, enregistrement,
 * e-mail de confirmation, vidage du panier.
 */
final class CheckoutService
{
    public function __construct(
        private readonly Cart $cart,
        private readonly OrderRepository $orders,
        private readonly PaymentGatewayInterface $gateway,
        private readonly Mailer $mailer,
        private readonly View $view,
        private readonly Url $url,
        private readonly Config $config
    ) {
    }

    /**
     * @param array{customer: Customer, shipping: string, payment: string, gift_wrap: bool, gift_message: string} $data
     *
     * @return array{order: Order, token: string, redirect?: string}
     */
    public function place(array $data): array
    {
        $this->cart->setOptions($data['shipping'], $data['gift_wrap']);
        $totals = $this->cart->totals();

        $reference = OrderReference::generate();
        while ($this->orders->referenceExists($reference)) {
            $reference = OrderReference::generate();
        }
        $token = OrderReference::token();

        $payment = $this->gateway->authorize($totals->total(), $data['payment'], $reference, $token);

        $items = array_map(static fn (CartItem $item): array => $item->toArray(), $this->cart->items());
        $totalsArray = $totals->toArray();
        $totalsArray['gift_message'] = $data['gift_message'];
        $totalsArray['payment_label'] = $payment->statusLabel();

        $order = $this->orders->create(
            $reference,
            $token,
            $data['customer'],
            $items,
            $totalsArray,
            $data['payment'],
            $payment->transactionReference()
        );

        $redirect = $payment->redirectUrl();
        if ($redirect === '') {
            $this->sendConfirmation($order, $token);
        }
        $this->cart->clear();

        $result = ['order' => $order, 'token' => $token];
        if ($redirect !== '') {
            $result['redirect'] = $redirect;
        }

        return $result;
    }

    /**
     * Corps de l'e-mail de confirmation (texte), réutilisé sur la page de confirmation.
     */
    public function confirmationEmail(Order $order, string $token): string
    {
        return $this->view->render('emails/order-confirmation', [
            'order' => $order,
            'orderUrl' => $this->url->absolute('/commande/' . $order->reference()) . '?t=' . $token,
            'shopEmail' => $this->config->string('shop.email'),
            'paymentMethods' => $this->config->array('shop.payment_methods'),
        ]);
    }

    private function sendConfirmation(Order $order, string $token): void
    {
        $subject = sprintf('Votre commande %s est confirmée – Azurine', $order->reference());
        $this->mailer->send($order->customer()->email(), $subject, $this->confirmationEmail($order, $token));
    }
}
