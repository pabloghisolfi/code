<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Exception\DatabaseException;
use Daris\Http\Response;

/**
 * Inscription à la lettre d'information (pied de page).
 */
final class NewsletterController extends AbstractController
{
    public function subscribe(): Response
    {
        $this->assertCsrf();
        $request = $this->services->request();
        $back = $request->input('back', '/', 200);
        if (!str_starts_with($back, '/') || str_starts_with($back, '//')) {
            $back = '/';
        }

        if ($this->honeypotTriggered()) {
            return $this->respond(true, 'Merci ! Vous êtes inscrite.', $back);
        }
        $this->assertRateLimit('newsletter', 5);

        $email = $request->input('email', '', 254);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return $this->respond(false, 'Cette adresse e-mail ne semble pas valide.', $back);
        }

        try {
            $new = $this->services->contacts()->subscribe($email);
        } catch (DatabaseException) {
            return $this->respond(false, 'Inscription impossible pour le moment. Réessayez plus tard.', $back);
        }

        return $this->respond(true, $new ? 'Merci ! Vous recevrez nos nouveautés et une surprise pour votre anniversaire.' : 'Cette adresse est déjà inscrite. À très vite !', $back);
    }

    private function respond(bool $ok, string $message, string $back): Response
    {
        if ($this->wantsJson()) {
            return Response::json(['ok' => $ok, 'message' => $message], $ok ? 200 : 422);
        }
        $this->flash($ok ? 'success' : 'error', $message);

        return $this->redirect($back, 'newsletter');
    }
}
