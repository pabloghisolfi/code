<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Http\Response;

/**
 * Conseillère de style : page de discussion et point d'accès JSON.
 */
final class AssistantController extends AbstractController
{
    public function index(): Response
    {
        $advisor = $this->services->advisor();

        return $this->page('assistant', [
            'greeting' => $advisor->greeting(),
            'suggestions' => $advisor->suggestions(),
            'faq' => $this->services->faq()->topics(),
        ], [
            'title' => 'Conseillère de style et questions fréquentes – Daris',
            'description' => 'Posez vos questions à Léa, la conseillère Daris : teintes, tailles de montres, livraison, cadeaux. Réponse immédiate, sans inscription.',
            'bodyClass' => 'page-assistant',
        ]);
    }

    public function reply(): Response
    {
        $request = $this->services->request();
        if (!$request->isSameOrigin()) {
            return Response::json(['ok' => false, 'error' => 'Requête refusée.'], 403);
        }
        if (!$this->services->csrf()->validate($request)) {
            return Response::json(['ok' => false, 'error' => 'Session expirée, rechargez la page.'], 419);
        }
        if ($this->services->rateLimiter()->tooManyAttempts('assistant', $request->ip(), 40)) {
            return Response::json(['ok' => false, 'error' => 'Beaucoup de questions d\'un coup ! Patientez quelques minutes.'], 429);
        }

        $payload = $request->json();
        $question = is_string($payload['question'] ?? null) ? trim($payload['question']) : '';
        $question = mb_substr(preg_replace('/[\x00-\x1F\x7F]/u', '', $question) ?? '', 0, 300);
        if ($question === '') {
            return Response::json(['ok' => false, 'error' => 'Écrivez une question.'], 422);
        }

        $reply = $this->services->advisor()->reply($question);
        $url = $this->services->url();

        return Response::json([
            'ok' => true,
            'answer' => $reply['answer'],
            'links' => array_map(
                static fn (array $link): array => ['label' => $link['label'], 'url' => $url->to($link['path'])],
                $reply['links']
            ),
        ]);
    }
}
