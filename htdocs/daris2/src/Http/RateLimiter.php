<?php

declare(strict_types=1);

namespace Daris\Http;

/**
 * Limitation de débit par fenêtre glissante, stockée dans des fichiers
 * (var/ratelimit). Suffisant pour protéger les formulaires d'un petit site
 * sans dépendre d'un service externe.
 */
final class RateLimiter
{
    public function __construct(
        private readonly string $directory,
        private readonly int $maxAttempts,
        private readonly int $windowSeconds
    ) {
    }

    /**
     * Enregistre une tentative et indique si la limite est dépassée.
     *
     * @param string $action     Nom de l'action (contact, commande, assistant…)
     * @param string $identifier Identifiant du client (adresse IP)
     */
    public function tooManyAttempts(string $action, string $identifier, ?int $maxAttempts = null): bool
    {
        $limit = $maxAttempts ?? $this->maxAttempts;
        $file = $this->file($action, $identifier);
        $now = time();

        if (!is_dir($this->directory) && !mkdir($this->directory, 0750, true) && !is_dir($this->directory)) {
            return false;
        }

        $handle = fopen($file, 'c+');
        if ($handle === false) {
            return false;
        }

        try {
            flock($handle, LOCK_EX);
            $raw = stream_get_contents($handle);
            $timestamps = $raw !== false && $raw !== '' ? json_decode($raw, true) : [];
            $timestamps = is_array($timestamps) ? array_values(array_filter(
                $timestamps,
                fn (mixed $t): bool => is_int($t) && $t > $now - $this->windowSeconds
            )) : [];

            $blocked = count($timestamps) >= $limit;
            if (!$blocked) {
                $timestamps[] = $now;
            }

            ftruncate($handle, 0);
            rewind($handle);
            fwrite($handle, json_encode($timestamps, JSON_THROW_ON_ERROR));
            fflush($handle);
            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        $this->collectGarbage();

        return $blocked;
    }

    public function retryAfterSeconds(): int
    {
        return $this->windowSeconds;
    }

    private function file(string $action, string $identifier): string
    {
        return $this->directory . '/' . hash('sha256', $action . '|' . $identifier) . '.json';
    }

    /**
     * Supprime de temps en temps les fichiers expirés (1 fois sur 50).
     */
    private function collectGarbage(): void
    {
        if (random_int(1, 50) !== 1) {
            return;
        }

        $files = glob($this->directory . '/*.json');
        if ($files === false) {
            return;
        }

        $limit = time() - $this->windowSeconds;
        foreach ($files as $file) {
            $mtime = filemtime($file);
            if (is_int($mtime) && $mtime < $limit) {
                unlink($file);
            }
        }
    }
}
