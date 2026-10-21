<?php

declare(strict_types=1);

namespace Daris\Contact;

use Daris\Database\Connection;

/**
 * Enregistrement des messages de contact et des inscriptions à la lettre
 * d'information (SQLite, requêtes préparées).
 */
final class ContactRepository
{
    public function __construct(private readonly Connection $db, private readonly string $secret)
    {
    }

    public function save(ContactMessage $message, string $ip): void
    {
        $this->db->run(
            'INSERT INTO contact_messages (name, email, subject, order_reference, message, ip_hash)
             VALUES (:name, :email, :subject, :order_reference, :message, :ip_hash)',
            [
                ':name' => $message->name(),
                ':email' => $message->email(),
                ':subject' => $message->subject(),
                ':order_reference' => $message->orderReference() === '' ? null : $message->orderReference(),
                ':message' => $message->message(),
                ':ip_hash' => hash_hmac('sha256', $ip, $this->secret),
            ]
        );
    }

    /**
     * Inscription idempotente : renvoie faux si l'adresse était déjà inscrite.
     */
    public function subscribe(string $email): bool
    {
        $email = mb_strtolower(trim($email));
        $exists = $this->db->run('SELECT 1 FROM newsletter WHERE email = :email', [':email' => $email])->fetch();
        if (is_array($exists)) {
            return false;
        }
        $this->db->run('INSERT INTO newsletter (email) VALUES (:email)', [':email' => $email]);

        return true;
    }
}
