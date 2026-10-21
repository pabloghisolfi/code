<?php

declare(strict_types=1);

namespace Daris\Order;

use Daris\Exception\ValidationException;
use Daris\Http\Request;

/**
 * Validation côté serveur du formulaire de commande.
 */
final class CheckoutValidator
{
    /**
     * @param array<string, mixed> $shippingMethods
     * @param array<string, mixed> $paymentMethods
     */
    public function __construct(
        private readonly array $shippingMethods,
        private readonly array $paymentMethods
    ) {
    }

    /**
     * @return array{customer: Customer, shipping: string, payment: string, gift_wrap: bool, gift_message: string}
     *
     * @throws ValidationException
     */
    public function validate(Request $request): array
    {
        $errors = [];

        $firstName = $request->input('first_name', '', 60);
        $lastName = $request->input('last_name', '', 60);
        $email = $request->input('email', '', 254);
        $phone = $request->input('phone', '', 30);
        $street = $request->input('street', '', 120);
        $postalCode = $request->input('postal_code', '', 10);
        $city = $request->input('city', '', 80);
        $canton = strtoupper($request->input('canton', '', 2));
        $note = $request->input('note', '', 500);
        $shipping = $request->input('shipping', 'economy', 20);
        $payment = $request->input('payment', '', 20);
        $giftWrap = $request->input('gift_wrap', '', 2) === '1';
        $giftMessage = $request->input('gift_message', '', 200);
        $terms = $request->input('terms', '', 2) === '1';

        if (mb_strlen($firstName) < 2 || preg_match('/^[\p{L}\p{M}\s\'’.-]+$/u', $firstName) !== 1) {
            $errors['first_name'] = 'Indiquez votre prénom.';
        }
        if (mb_strlen($lastName) < 2 || preg_match('/^[\p{L}\p{M}\s\'’.-]+$/u', $lastName) !== 1) {
            $errors['last_name'] = 'Indiquez votre nom.';
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || preg_match('/[\r\n]/', $email) === 1) {
            $errors['email'] = 'Cette adresse e-mail ne semble pas valide.';
        }
        if ($phone !== '' && preg_match('/^\+?[0-9 ().-]{7,20}$/', $phone) !== 1) {
            $errors['phone'] = 'Numéro de téléphone invalide (ex. +41 79 123 45 67).';
        }
        if (mb_strlen($street) < 4) {
            $errors['street'] = 'Indiquez votre rue et numéro.';
        }
        if (preg_match('/^(\d{4}|[A-Za-z]{2}-\d{4})$/', $postalCode) !== 1) {
            $errors['postal_code'] = 'Le NPA suisse comporte 4 chiffres.';
        }
        if (mb_strlen($city) < 2) {
            $errors['city'] = 'Indiquez votre localité.';
        }
        if (!isset(Customer::CANTONS[$canton])) {
            $errors['canton'] = 'Choisissez votre canton.';
        }
        if (!isset($this->shippingMethods[$shipping])) {
            $errors['shipping'] = 'Choisissez un mode de livraison.';
        }
        if (!isset($this->paymentMethods[$payment])) {
            $errors['payment'] = 'Choisissez un moyen de paiement.';
        }
        if (!$terms) {
            $errors['terms'] = 'Merci d\'accepter les conditions générales de vente.';
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        $customer = new Customer($firstName, $lastName, $email, $phone, $street, $postalCode, $city, $canton, $note);

        return [
            'customer' => $customer,
            'shipping' => $shipping,
            'payment' => $payment,
            'gift_wrap' => $giftWrap,
            'gift_message' => $giftWrap ? $giftMessage : '',
        ];
    }
}
