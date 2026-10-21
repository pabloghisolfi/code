<?php

declare(strict_types=1);

namespace Daris\Order;

/**
 * Coordonnées de livraison et de facturation d'une commande.
 */
final class Customer
{
    public const CANTONS = [
        'AG' => 'Argovie', 'AI' => 'Appenzell Rhodes-Intérieures', 'AR' => 'Appenzell Rhodes-Extérieures',
        'BE' => 'Berne', 'BL' => 'Bâle-Campagne', 'BS' => 'Bâle-Ville', 'FR' => 'Fribourg', 'GE' => 'Genève',
        'GL' => 'Glaris', 'GR' => 'Grisons', 'JU' => 'Jura', 'LU' => 'Lucerne', 'NE' => 'Neuchâtel',
        'NW' => 'Nidwald', 'OW' => 'Obwald', 'SG' => 'Saint-Gall', 'SH' => 'Schaffhouse', 'SO' => 'Soleure',
        'SZ' => 'Schwytz', 'TG' => 'Thurgovie', 'TI' => 'Tessin', 'UR' => 'Uri', 'VD' => 'Vaud', 'VS' => 'Valais',
        'ZG' => 'Zoug', 'ZH' => 'Zurich', 'FL' => 'Liechtenstein',
    ];

    public function __construct(
        private readonly string $firstName,
        private readonly string $lastName,
        private readonly string $email,
        private readonly string $phone,
        private readonly string $street,
        private readonly string $postalCode,
        private readonly string $city,
        private readonly string $canton,
        private readonly string $note
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $s = static fn (string $k): string => is_string($data[$k] ?? null) ? $data[$k] : '';

        return new self(
            $s('first_name'),
            $s('last_name'),
            $s('email'),
            $s('phone'),
            $s('street'),
            $s('postal_code'),
            $s('city'),
            $s('canton'),
            $s('note')
        );
    }

    public function fullName(): string
    {
        return trim($this->firstName . ' ' . $this->lastName);
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function street(): string
    {
        return $this->street;
    }

    public function postalCode(): string
    {
        return $this->postalCode;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function canton(): string
    {
        return $this->canton;
    }

    public function cantonName(): string
    {
        return self::CANTONS[$this->canton] ?? $this->canton;
    }

    public function note(): string
    {
        return $this->note;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone,
            'street' => $this->street,
            'postal_code' => $this->postalCode,
            'city' => $this->city,
            'canton' => $this->canton,
            'note' => $this->note,
        ];
    }
}
