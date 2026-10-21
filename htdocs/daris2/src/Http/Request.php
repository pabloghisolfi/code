<?php

declare(strict_types=1);

namespace Daris\Http;

/**
 * Représentation immuable de la requête HTTP entrante.
 *
 * Centralise la lecture (et le nettoyage) des superglobales pour que le reste
 * du code n'y touche jamais directement.
 */
final class Request
{
    private const MAX_JSON_BODY = 16384;

    /**
     * @param array<string, mixed>  $query
     * @param array<string, mixed>  $post
     * @param array<string, mixed>  $server
     * @param array<string, string> $cookies
     */
    private function __construct(
        private readonly string $method,
        private readonly string $path,
        private readonly array $query,
        private readonly array $post,
        private readonly array $server,
        private readonly array $cookies,
        private readonly string $rawBody
    ) {
    }

    /**
     * Construit la requête à partir des superglobales, en retirant le préfixe
     * de dossier (/daris2) du chemin.
     */
    public static function fromGlobals(string $basePath): self
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = (string) (parse_url($uri, PHP_URL_PATH) ?? '/');
        $path = rawurldecode($path);

        if ($basePath !== '' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }
        if (str_starts_with($path, '/index.php')) {
            $path = substr($path, strlen('/index.php'));
        }

        $path = '/' . trim(preg_replace('#/+#', '/', $path) ?? '', '/');
        $method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
        $raw = $method === 'POST' ? (string) file_get_contents('php://input', false, null, 0, self::MAX_JSON_BODY + 1) : '';

        /** @var array<string, string> $cookies */
        $cookies = array_filter($_COOKIE, 'is_string');

        return new self($method, $path, $_GET, $_POST, $_SERVER, $cookies, $raw);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * Paramètre de requête (GET) nettoyé : chaîne tronquée, sans caractères de contrôle.
     */
    public function query(string $key, string $default = '', int $maxLength = 200): string
    {
        return $this->clean($this->query[$key] ?? null, $default, $maxLength);
    }

    public function queryInt(string $key, int $default = 0, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        $raw = $this->query[$key] ?? null;
        if (!is_scalar($raw) || preg_match('/^-?\d{1,9}$/', (string) $raw) !== 1) {
            return $default;
        }

        return max($min, min($max, (int) $raw));
    }

    /**
     * Liste de valeurs GET (ex. marque[] pour filtres multi-sélection).
     *
     * @return list<string>
     */
    public function queryList(string $key, int $maxItems = 30, int $maxLength = 80): array
    {
        $raw = $this->query[$key] ?? null;
        $items = [];
        if (is_array($raw)) {
            $items = $raw;
        } elseif (is_string($raw) && $raw !== '') {
            $items = [$raw];
        }

        $out = [];
        foreach (array_slice($items, 0, $maxItems) as $item) {
            if (!is_scalar($item)) {
                continue;
            }
            $value = $this->clean((string) $item, '', $maxLength);
            if ($value !== '') {
                $out[] = $value;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * @return array<string, mixed>
     */
    public function allQuery(): array
    {
        return $this->query;
    }

    /**
     * Champ de formulaire (POST) nettoyé.
     */
    public function input(string $key, string $default = '', int $maxLength = 5000): string
    {
        return $this->clean($this->post[$key] ?? null, $default, $maxLength);
    }

    public function inputInt(string $key, int $default = 0, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX): int
    {
        $raw = $this->post[$key] ?? null;
        if (!is_scalar($raw) || preg_match('/^-?\d{1,9}$/', (string) $raw) !== 1) {
            return $default;
        }

        return max($min, min($max, (int) $raw));
    }

    public function hasInput(string $key): bool
    {
        return array_key_exists($key, $this->post);
    }

    /**
     * @return array<string, mixed>
     */
    public function allInput(): array
    {
        return $this->post;
    }

    /**
     * Corps JSON décodé (ou tableau vide si absent/invalide/trop long).
     *
     * @return array<string, mixed>
     */
    public function json(): array
    {
        if ($this->rawBody === '' || strlen($this->rawBody) > self::MAX_JSON_BODY) {
            return [];
        }

        $decoded = json_decode($this->rawBody, true, 8);

        return is_array($decoded) ? $decoded : [];
    }

    public function wantsJson(): bool
    {
        $accept = strtolower($this->header('Accept'));
        $contentType = strtolower($this->header('Content-Type'));

        return str_contains($accept, 'application/json')
            || str_contains($contentType, 'application/json')
            || $this->header('X-Requested-With') === 'fetch';
    }

    public function header(string $name): string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        if ($name === 'Content-Type') {
            $key = 'CONTENT_TYPE';
        }
        $value = $this->server[$key] ?? '';

        return is_string($value) ? $value : '';
    }

    public function cookie(string $name): string
    {
        return $this->cookies[$name] ?? '';
    }

    public function isHttps(): bool
    {
        $https = $this->server['HTTPS'] ?? '';
        if (is_string($https) && $https !== '' && strtolower($https) !== 'off') {
            return true;
        }

        return strtolower($this->header('X-Forwarded-Proto')) === 'https';
    }

    /**
     * Hôte validé (lettres, chiffres, points, tirets et port facultatif).
     */
    public function host(): string
    {
        $host = (string) ($this->server['HTTP_HOST'] ?? '');

        return preg_match('/^[a-z0-9.-]+(?::\d{1,5})?$/i', $host) === 1 ? $host : '';
    }

    /**
     * Adresse IP cliente (sans faire confiance aux en-têtes proxy, falsifiables).
     */
    public function ip(): string
    {
        $ip = (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');

        return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : '0.0.0.0';
    }

    /**
     * Vrai si l'en-tête Origin/Referer désigne ce même site (défense anti-CSRF
     * complémentaire aux jetons, et contrôle des appels JSON).
     */
    public function isSameOrigin(): bool
    {
        $host = $this->host();
        if ($host === '') {
            return false;
        }

        foreach (['Origin', 'Referer'] as $header) {
            $value = $this->header($header);
            if ($value === '') {
                continue;
            }
            $candidate = (string) parse_url($value, PHP_URL_HOST);
            $port = parse_url($value, PHP_URL_PORT);
            if (is_int($port)) {
                $candidate .= ':' . $port;
            }

            return strcasecmp($candidate, $host) === 0;
        }

        return false;
    }

    private function clean(mixed $raw, string $default, int $maxLength): string
    {
        if (!is_scalar($raw)) {
            return $default;
        }

        $value = (string) $raw;
        if (!mb_check_encoding($value, 'UTF-8')) {
            return $default;
        }

        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value) ?? '';

        return trim(mb_substr($value, 0, $maxLength));
    }
}
