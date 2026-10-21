<?php

declare(strict_types=1);

namespace Daris\Contact;

/**
 * Message validé du formulaire de contact.
 */
final class ContactMessage
{
    public const SUBJECTS = [
        'commande' => 'Une commande en cours',
        'conseil' => 'Un conseil produit ou teinte',
        'retour' => 'Un retour ou un échange',
        'montre' => 'Service atelier (pile, bracelet, garantie)',
        'autre' => 'Autre chose',
    ];

    public function __construct(
        private readonly string $name,
        private readonly string $email,
        private readonly string $subject,
        private readonly string $orderReference,
        private readonly string $message
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function subjectLabel(): string
    {
        return self::SUBJECTS[$this->subject] ?? $this->subject;
    }

    public function orderReference(): string
    {
        return $this->orderReference;
    }

    public function message(): string
    {
        return $this->message;
    }
}
