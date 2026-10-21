<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Exception\DatabaseException;
use Daris\Exception\NotFoundException;
use Daris\Exception\PaymentException;
use Daris\Exception\ValidationException;
use Daris\Http\Response;
use Daris\Order\CheckoutValidator;
use Daris\Order\Customer;
use Daris\Order\OrderReference;

/**
 * Tunnel de commande : formulaire, validation, confirmation et suivi.
 */
final class CheckoutController extends AbstractController
{
    public function form(): Response
    {
        $cart = $this->services->cart();
        if ($cart->isEmpty()) {
            $this->flash('info', 'Votre panier est vide : choisissez d\'abord un produit.');

            return $this->redirect('/boutique');
        }

        $session = $this->services->session();
        $old = $session->pullOldInput();
        $old += $this->previousCustomer();

        return $this->page('checkout', [
            'items' => $cart->items(),
            'totals' => $cart->totals(),
            'old' => $old,
            'errors' => $session->pullErrors(),
            'cantons' => Customer::CANTONS,
            'shippingMethods' => $this->services->config()->array('shop.shipping_methods'),
            'paymentMethods' => $this->services->config()->array('shop.payment_methods'),
            'giftWrapPrice' => $this->services->config()->int('shop.gift_wrap_price'),
            'freeShippingFrom' => $this->services->config()->int('shop.free_shipping_from'),
            'selectedShipping' => $old['shipping'] ?? $cart->shippingMethod(),
            'giftWrap' => isset($old['gift_wrap']) ? $old['gift_wrap'] === '1' : $cart->giftWrap(),
        ], [
            'title' => 'Commande – Daris',
            'description' => 'Finalisez votre commande Daris en toute sécurité.',
            'noindex' => true,
            'bodyClass' => 'page-checkout',
        ]);
    }

    public function submit(): Response
    {
        $this->assertCsrf();
        $request = $this->services->request();
        $cart = $this->services->cart();

        if ($cart->isEmpty()) {
            return $this->redirect('/boutique');
        }
        if ($this->honeypotTriggered()) {
            return $this->redirect('/panier');
        }
        $this->assertRateLimit('commande', 8);

        $config = $this->services->config();
        $validator = new CheckoutValidator($config->array('shop.shipping_methods'), $config->array('shop.payment_methods'));

        $remembered = array_filter($request->allInput(), 'is_string');
        unset($remembered['_token'], $remembered['website']);

        try {
            $data = $validator->validate($request);
        } catch (ValidationException $exception) {
            $this->services->session()->rememberInput($remembered);
            $this->services->session()->rememberErrors($exception->getErrors());
            $this->flash('error', 'Quelques champs demandent votre attention.');

            return $this->redirect('/commande', 'formulaire');
        }

        // Mise à jour immédiate des options (livraison, emballage) pour le récapitulatif.
        $cart->setOptions($data['shipping'], $data['gift_wrap']);

        try {
            $result = $this->services->checkout()->place($data);
        } catch (PaymentException $exception) {
            $this->services->session()->rememberInput($remembered);
            $this->flash('error', 'Le paiement n\'a pas abouti : ' . $exception->getMessage());

            return $this->redirect('/commande', 'paiement');
        } catch (DatabaseException) {
            $this->services->session()->rememberInput($remembered);
            $this->flash('error', 'La commande n\'a pas pu être enregistrée. Réessayez dans un instant ou écrivez-nous.');

            return $this->redirect('/commande');
        }

        $session = $this->services->session();
        $session->regenerate();
        $session->set('last_customer', $data['customer']->toArray());

        return Response::redirect(
            $this->services->url()->to('/commande/' . $result['order']->reference(), ['t' => $result['token']])
        );
    }

    public function confirmation(string $reference): Response
    {
        $token = $this->services->request()->query('t', '', 64);
        if (!OrderReference::isValid($reference) || preg_match('/^[a-f0-9]{40}$/', $token) !== 1) {
            throw new NotFoundException('Cette commande est introuvable.');
        }

        $order = $this->services->orders()->findByToken($reference, $token);
        if ($order === null) {
            throw new NotFoundException('Cette commande est introuvable ou le lien n\'est plus valable.');
        }

        return $this->page('confirmation', [
            'order' => $order,
            'email' => $this->services->checkout()->confirmationEmail($order, $token),
            'paymentMethods' => $this->services->config()->array('shop.payment_methods'),
            'justPlaced' => $this->services->request()->header('Referer') !== '' && str_contains($this->services->request()->header('Referer'), '/commande'),
        ], [
            'title' => 'Commande ' . $order->reference() . ' confirmée – Daris',
            'description' => 'Merci pour votre commande.',
            'noindex' => true,
            'bodyClass' => 'page-confirmation',
        ]);
    }

    public function trackingForm(): Response
    {
        return $this->page('tracking', [
            'order' => null,
            'old' => $this->services->session()->pullOldInput(),
            'errors' => $this->services->session()->pullErrors(),
        ], [
            'title' => 'Suivre ma commande – Daris',
            'description' => 'Retrouvez l\'état de votre commande Daris avec votre référence et votre e-mail.',
            'bodyClass' => 'page-tracking',
        ]);
    }

    public function track(): Response
    {
        $this->assertCsrf();
        if ($this->honeypotTriggered()) {
            return $this->redirect('/commande/suivi');
        }
        $this->assertRateLimit('suivi', 10);

        $request = $this->services->request();
        $reference = strtoupper($request->input('reference', '', 20));
        $email = $request->input('email', '', 254);
        $errors = [];

        if (!OrderReference::isValid($reference)) {
            $errors['reference'] = 'La référence ressemble à DA-2026-ABC123.';
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $errors['email'] = 'Indiquez l\'adresse e-mail utilisée pour la commande.';
        }

        $order = $errors === [] ? $this->services->orders()->findByReferenceAndEmail($reference, $email) : null;
        if ($order === null && $errors === []) {
            $errors['reference'] = 'Aucune commande ne correspond à cette référence et cette adresse.';
        }

        return $this->page('tracking', [
            'order' => $order,
            'old' => ['reference' => $reference, 'email' => $email],
            'errors' => $errors,
        ], [
            'title' => 'Suivre ma commande – Daris',
            'description' => 'Retrouvez l\'état de votre commande Daris.',
            'noindex' => true,
            'bodyClass' => 'page-tracking',
        ], $errors === [] ? 200 : 422);
    }

    /**
     * Coordonnées de la dernière commande de la session, pour préremplir.
     *
     * @return array<string, string>
     */
    private function previousCustomer(): array
    {
        $previous = $this->services->session()->getArray('last_customer');

        return array_filter($previous, 'is_string');
    }
}
