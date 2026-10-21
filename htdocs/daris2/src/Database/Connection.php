<?php

declare(strict_types=1);

namespace Daris\Database;

use Daris\Exception\DatabaseException;
use PDO;
use PDOException;

/**
 * Connexion SQLite paresseuse (ouverte au premier usage) avec schéma
 * créé automatiquement. Toutes les requêtes passent par des requêtes préparées.
 */
final class Connection
{
    private ?PDO $pdo = null;

    public function __construct(private readonly string $file)
    {
    }

    public function pdo(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }

        $directory = dirname($this->file);
        if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
            throw new DatabaseException('Le dossier de la base de données ne peut pas être créé.');
        }

        try {
            $pdo = new PDO('sqlite:' . $this->file, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            $pdo->exec('PRAGMA journal_mode = WAL');
            $pdo->exec('PRAGMA foreign_keys = ON');
            $pdo->exec('PRAGMA busy_timeout = 3000');
        } catch (PDOException $exception) {
            throw new DatabaseException('Impossible d\'ouvrir la base de données.', 0, $exception);
        }

        $this->pdo = $pdo;
        Schema::ensure($pdo);

        return $pdo;
    }

    /**
     * Prépare et exécute une requête ; renvoie la déclaration pour lecture.
     *
     * @param array<string, scalar|null> $params
     */
    public function run(string $sql, array $params = []): \PDOStatement
    {
        try {
            $statement = $this->pdo()->prepare($sql);
            $statement->execute($params);

            return $statement;
        } catch (PDOException $exception) {
            throw new DatabaseException('La requête a échoué.', 0, $exception);
        }
    }
}
