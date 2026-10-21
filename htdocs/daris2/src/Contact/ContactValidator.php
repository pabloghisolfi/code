<?php

declare(strict_types=1);

namespace Daris\Contact;

use Daris\Exception\ValidationException;
use Daris\Http\Request;
use Daris\Order\OrderReference;

/**
 * Validation du formulaire de contact (longueurs, formats, anti-spam basique).
 */
final class ContactValidator
{
    private const SPAM_PATTERNS = ['/https?:\/\/[^\s]+\s+https?:\/\//i', '/\[url=/i', '/<a\s+href/i'];

    /**
     * @throws ValidationException
     */
    public function validate(Request $request): ContactMessage
    {
        $errors = [];
        $name = $request->input('name', '', 80);
        $email = $request->input('email', '', 254);
        $subject = $request->input('subject', '', 20);
        $orderReference = strtoupper($request->input('order_reference', '', 20));
        $message = $request->input('message', '', 4000);

        if (mb_strlen($name) < 2 || preg_match('/^[\p{L}\p{M}\s\'’.-]+$/u', $name) !== 1) {
            $errors['name'] = 'Indiquez votre nom (lettres uniquement).';
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || preg_match('/[\r\n]/', $email) === 1) {
            $errors['email'] = 'Cette adresse e-mail ne semble pas valide.';
        }
        if (!isset(ContactMessage::SUBJECTS[$subject])) {
            $errors['subject'] = 'Choisissez un sujet.';
        }
        if ($orderReference !== '' && !OrderReference::isValid($orderReference)) {
            $errors['order_reference'] = 'La référence ressemble à DA-2026-ABC123.';
        }
        if (mb_strlen($message) < 10) {
            $errors['message'] = 'Votre message est un peu court (10 caractères minimum).';
        }
        foreach (self::SPAM_PATTERNS as $pattern) {
            if (preg_match($pattern, $message) === 1) {
                $errors['message'] = 'Le message contient trop de liens.';
                break;
            }
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return new ContactMessage($name, $email, $subject, $orderReference, $message);
    }
}
