<?php

declare(strict_types=1);

namespace Daris\Database;

use Daris\Exception\DatabaseException;
use PDO;
use PDOException;

/**
 * Schéma SQLite : commandes, messages de contact, inscriptions à la lettre.
 */
final class Schema
{
    private const STATEMENTS = [
        'CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            reference TEXT NOT NULL UNIQUE,
            token_hash TEXT NOT NULL,
            email TEXT NOT NULL,
            customer_json TEXT NOT NULL,
            items_json TEXT NOT NULL,
            totals_json TEXT NOT NULL,
            payment_method TEXT NOT NULL,
            payment_reference TEXT,
            status TEXT NOT NULL DEFAULT "confirmee",
            created_at TEXT NOT NULL DEFAULT (datetime("now"))
        )',
        'CREATE INDEX IF NOT EXISTS idx_orders_email ON orders (email)',
        'CREATE TABLE IF NOT EXISTS contact_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            subject TEXT NOT NULL,
            order_reference TEXT,
            message TEXT NOT NULL,
            ip_hash TEXT NOT NULL,
            created_at TEXT NOT NULL DEFAULT (datetime("now"))
        )',
        'CREATE TABLE IF NOT EXISTS newsletter (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            created_at TEXT NOT NULL DEFAULT (datetime("now"))
        )',
    ];

    private function __construct()
    {
    }

    public static function ensure(PDO $pdo): void
    {
        try {
            foreach (self::STATEMENTS as $sql) {
                $pdo->exec($sql);
            }
        } catch (PDOException $exception) {
            throw new DatabaseException('Le schéma de la base n\'a pas pu être créé.', 0, $exception);
        }
    }
}
