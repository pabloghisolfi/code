<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Contact\ContactMessage;
use Daris\Contact\ContactValidator;
use Daris\Exception\DatabaseException;
use Daris\Exception\ValidationException;
use Daris\Http\Response;

/**
 * Formulaire de contact : validation serveur, anti-spam, enregistrement,
 * copie par e-mail à la boutique et accusé de réception.
 */
final class ContactController extends AbstractController
{
    public function form(): Response
    {
        $session = $this->services->session();
        $request = $this->services->request();
        $old = $session->pullOldInput();
        $preset = $request->query('sujet', '', 20);
        if ($old === [] && isset(ContactMessage::SUBJECTS[$preset])) {
            $old['subject'] = $preset;
        }
        $reference = $request->query('commande', '', 20);
        if ($old === [] && $reference !== '') {
            $old['order_reference'] = strtoupper($reference);
        }

        return $this->page('contact', [
            'subjects' => ContactMessage::SUBJECTS,
            'old' => $old,
            'errors' => $session->pullErrors(),
            'sent' => $request->query('envoye', '', 1) === '1',
        ], [
            'title' => 'Contact – Daris',
            'description' => 'Une question sur une teinte, une commande ou une montre ? L\'équipe Daris répond sous 24 heures ouvrées.',
            'bodyClass' => 'page-contact',
        ]);
    }

    public function submit(): Response
    {
        $this->assertCsrf();
        if ($this->honeypotTriggered()) {
            // Robot probable : on fait comme si tout s'était bien passé.
            return Response::redirect($this->services->url()->to('/contact', ['envoye' => '1']));
        }
        $this->assertRateLimit('contact');

        $request = $this->services->request();
        $remembered = array_filter($request->allInput(), 'is_string');
        unset($remembered['_token'], $remembered['website']);

        try {
            $message = (new ContactValidator())->validate($request);
        } catch (ValidationException $exception) {
            $this->services->session()->rememberInput($remembered);
            $this->services->session()->rememberErrors($exception->getErrors());
            $this->flash('error', 'Quelques champs demandent votre attention.');

            return $this->redirect('/contact', 'formulaire');
        }

        try {
            $this->services->contacts()->save($message, $request->ip());
        } catch (DatabaseException) {
            $this->services->session()->rememberInput($remembered);
            $this->flash('error', 'Votre message n\'a pas pu être enregistré. Réessayez dans un instant ou écrivez-nous directement par e-mail.');

            return $this->redirect('/contact', 'formulaire');
        }

        $this->notify($message);

        return Response::redirect($this->services->url()->to('/contact', ['envoye' => '1']));
    }

    private function notify(ContactMessage $message): void
    {
        $mailer = $this->services->mailer();
        $shopEmail = $this->services->config()->string('shop.email');
        $view = $this->services->view();

        $mailer->send(
            $shopEmail,
            '[Daris] ' . $message->subjectLabel() . ' – ' . $message->name(),
            $view->render('emails/contact-notification', ['message' => $message])
        );
        $mailer->send(
            $message->email(),
            'Nous avons bien reçu votre message – Daris',
            $view->render('emails/contact-receipt', ['message' => $message, 'shopEmail' => $shopEmail])
        );
    }
}
