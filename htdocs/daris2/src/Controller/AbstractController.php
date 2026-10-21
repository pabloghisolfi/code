<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Exception\HttpException;
use Daris\Http\Response;
use Daris\Kernel\Services;

/**
 * Socle des contrôleurs : accès aux services et réponses courantes.
 */
abstract class AbstractController
{
    public function __construct(protected readonly Services $services)
    {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $meta
     */
    protected function page(string $template, array $data = [], array $meta = [], int $status = 200): Response
    {
        $meta['canonical'] ??= $this->services->url()->absolute($this->services->request()->path());

        return Response::html($this->services->view()->page($template, $data, $meta), $status);
    }

    protected function redirect(string $path, string $fragment = ''): Response
    {
        return Response::redirect($this->services->url()->to($path) . ($fragment !== '' ? '#' . $fragment : ''));
    }

    protected function flash(string $type, string $message): void
    {
        $this->services->session()->flash($type, $message);
    }

    /**
     * Vérifie le jeton CSRF ; renvoie 419 sinon (ou une erreur JSON).
     *
     * @throws HttpException
     */
    protected function assertCsrf(): void
    {
        if (!$this->services->csrf()->validate($this->services->request())) {
            throw new HttpException(419, 'Votre session a expiré. Rechargez la page et réessayez.');
        }
    }

    /**
     * Champ piège (honeypot) : un robot qui le remplit est ignoré silencieusement.
     */
    protected function honeypotTriggered(): bool
    {
        $request = $this->services->request();

        return $request->input('website', '', 200) !== '' || $request->input('_time', '', 20) === '';
    }

    /**
     * Applique la limitation de débit à une action de formulaire.
     *
     * @throws HttpException 429
     */
    protected function assertRateLimit(string $action, ?int $maxAttempts = null): void
    {
        $limiter = $this->services->rateLimiter();
        if ($limiter->tooManyAttempts($action, $this->services->request()->ip(), $maxAttempts)) {
            throw new HttpException(429, 'Trop de tentatives. Patientez quelques minutes avant de réessayer.');
        }
    }

    protected function wantsJson(): bool
    {
        return $this->services->request()->wantsJson();
    }
}
