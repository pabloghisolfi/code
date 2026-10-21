<?php

declare(strict_types=1);

namespace Daris\Order;

use Daris\Database\Connection;
use JsonException;

/**
 * Persistance des commandes en SQLite (requêtes préparées uniquement).
 *
 * Le jeton d'accès au récapitulatif n'est stocké que haché : le lien envoyé
 * à la cliente reste le seul exemplaire du secret.
 */
final class OrderRepository
{
    public function __construct(private readonly Connection $db, private readonly string $secret)
    {
    }

    /**
     * @param list<array{product_id: int, slug: string, name: string, variant: ?string, quantity: int, unit_price: int, total: int}> $items
     * @param array<string, int|float|string|null> $totals
     */
    public function create(
        string $reference,
        string $token,
        Customer $customer,
        array $items,
        array $totals,
        string $paymentMethod,
        ?string $paymentReference
    ): Order {
        $this->db->run(
            'INSERT INTO orders (reference, token_hash, email, customer_json, items_json, totals_json, payment_method, payment_reference)
             VALUES (:reference, :token_hash, :email, :customer, :items, :totals, :payment_method, :payment_reference)',
            [
                ':reference' => $reference,
                ':token_hash' => $this->hashToken($token),
                ':email' => mb_strtolower($customer->email()),
                ':customer' => json_encode($customer->toArray(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                ':items' => json_encode($items, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                ':totals' => json_encode($totals, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
                ':payment_method' => $paymentMethod,
                ':payment_reference' => $paymentReference,
            ]
        );

        return new Order($reference, $customer, $items, $totals, $paymentMethod, $paymentReference, 'confirmee', gmdate('Y-m-d H:i:s'));
    }

    /**
     * Commande accessible par son lien signé (référence + jeton).
     */
    public function findByToken(string $reference, string $token): ?Order
    {
        $row = $this->db->run('SELECT * FROM orders WHERE reference = :reference LIMIT 1', [':reference' => $reference])->fetch();
        if (!is_array($row) || !hash_equals((string) $row['token_hash'], $this->hashToken($token))) {
            return null;
        }

        return $this->hydrate($row);
    }

    /**
     * Suivi : référence + e-mail de la commande.
     */
    public function findByReferenceAndEmail(string $reference, string $email): ?Order
    {
        $row = $this->db->run(
            'SELECT * FROM orders WHERE reference = :reference AND email = :email LIMIT 1',
            [':reference' => $reference, ':email' => mb_strtolower(trim($email))]
        )->fetch();

        return is_array($row) ? $this->hydrate($row) : null;
    }

    public function referenceExists(string $reference): bool
    {
        $row = $this->db->run('SELECT 1 FROM orders WHERE reference = :reference', [':reference' => $reference])->fetch();

        return is_array($row);
    }

    private function hashToken(string $token): string
    {
        return hash_hmac('sha256', $token, $this->secret);
    }

    /**
     * @param array<string, mixed> $row
     */
    private function hydrate(array $row): Order
    {
        try {
            $customer = json_decode((string) $row['customer_json'], true, 4, JSON_THROW_ON_ERROR);
            $items = json_decode((string) $row['items_json'], true, 4, JSON_THROW_ON_ERROR);
            $totals = json_decode((string) $row['totals_json'], true, 4, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            $customer = [];
            $items = [];
            $totals = [];
        }

        /** @var list<array{product_id: int, slug: string, name: string, variant: ?string, quantity: int, unit_price: int, total: int}> $items */
        $items = is_array($items) ? array_values($items) : [];

        return new Order(
            (string) $row['reference'],
            Customer::fromArray(is_array($customer) ? $customer : []),
            $items,
            is_array($totals) ? $totals : [],
            (string) $row['payment_method'],
            isset($row['payment_reference']) ? (string) $row['payment_reference'] : null,
            (string) $row['status'],
            (string) $row['created_at']
        );
    }
}
