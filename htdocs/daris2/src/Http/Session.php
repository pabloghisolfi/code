<?php

declare(strict_types=1);

namespace Daris\Http;

/**
 * Session PHP durcie : cookie HttpOnly, SameSite, Secure en HTTPS, mode strict,
 * régénération périodique de l'identifiant, messages flash.
 */
final class Session
{
    private const REGENERATE_AFTER = 900;

    private bool $started = false;

    public function __construct(
        private readonly string $name,
        private readonly int $lifetime,
        private readonly string $cookiePath,
        private readonly bool $secure
    ) {
    }

    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;

            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.gc_maxlifetime', (string) $this->lifetime);

        session_name($this->name);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => $this->cookiePath,
            'domain' => '',
            'secure' => $this->secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
        $this->started = true;

        $now = time();
        $created = $this->getInt('_created');
        if ($created === 0) {
            $_SESSION['_created'] = $now;
        } elseif ($now - $created > self::REGENERATE_AFTER) {
            $this->regenerate();
        }
    }

    /**
     * Nouvel identifiant de session (après une action sensible, par ex. une commande).
     */
    public function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['_created'] = time();
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function getInt(string $key): int
    {
        $value = $_SESSION[$key] ?? 0;

        return is_int($value) ? $value : 0;
    }

    public function getString(string $key): string
    {
        $value = $_SESSION[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    /**
     * @return array<mixed>
     */
    public function getArray(string $key): array
    {
        $value = $_SESSION[$key] ?? [];

        return is_array($value) ? $value : [];
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Message affiché une seule fois sur la page suivante.
     */
    public function flash(string $type, string $message): void
    {
        $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
    }

    /**
     * @return array{type: string, message: string}|null
     */
    public function pullFlash(): ?array
    {
        $flash = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);

        if (!is_array($flash) || !isset($flash['type'], $flash['message'])) {
            return null;
        }

        return ['type' => (string) $flash['type'], 'message' => (string) $flash['message']];
    }

    /**
     * Mémorise les champs saisis pour les réafficher après une erreur de validation.
     *
     * @param array<string, string> $values
     */
    public function rememberInput(array $values): void
    {
        $_SESSION['_old'] = $values;
    }

    /**
     * @return array<string, string>
     */
    public function pullOldInput(): array
    {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);

        return is_array($old) ? array_filter($old, 'is_string') : [];
    }

    /**
     * @param array<string, string> $errors
     */
    public function rememberErrors(array $errors): void
    {
        $_SESSION['_errors'] = $errors;
    }

    /**
     * @return array<string, string>
     */
    public function pullErrors(): array
    {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);

        return is_array($errors) ? array_filter($errors, 'is_string') : [];
    }
}
