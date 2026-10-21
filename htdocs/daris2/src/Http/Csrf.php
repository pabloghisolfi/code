<?php

declare(strict_types=1);

namespace Daris\Http;

/**
 * Jetons anti-CSRF liés à la session, comparés en temps constant.
 */
final class Csrf
{
    public const FIELD = '_token';
    public const HEADER = 'X-CSRF-Token';

    private const SESSION_KEY = '_csrf';

    public function __construct(private readonly Session $session)
    {
    }

    public function token(): string
    {
        $token = $this->session->getString(self::SESSION_KEY);
        if ($token === '') {
            $token = bin2hex(random_bytes(32));
            $this->session->set(self::SESSION_KEY, $token);
        }

        return $token;
    }

    /**
     * Valide le jeton envoyé en champ de formulaire ou en en-tête (appels JSON).
     */
    public function validate(Request $request): bool
    {
        $expected = $this->session->getString(self::SESSION_KEY);
        if ($expected === '') {
            return false;
        }

        $provided = $request->input(self::FIELD, '', 128);
        if ($provided === '') {
            $provided = $request->header(self::HEADER);
        }
        if ($provided === '') {
            $json = $request->json();
            $provided = is_string($json[self::FIELD] ?? null) ? $json[self::FIELD] : '';
        }

        return $provided !== '' && hash_equals($expected, $provided);
    }

    /**
     * Champ caché prêt à insérer dans un formulaire.
     */
    public function field(): string
    {
        return sprintf(
            '<input type="hidden" name="%s" value="%s">',
            self::FIELD,
            htmlspecialchars($this->token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
        );
    }
}
