<?php

declare(strict_types=1);

namespace Daris\Controller;

use Daris\Http\Response;
use Daris\I18n\Locale;

/**
 * Changement de langue d’interface (session).
 */
final class LocaleController extends AbstractController
{
    public function set(): Response
    {
        $this->assertCsrf();
        $code = $this->services->request()->input('locale', '', 5);
        if (Locale::isSupported($code)) {
            $this->services->session()->set('locale', $code);
        }

        $back = $this->services->request()->input('back', '/', 200);
        if (!str_starts_with($back, '/')) {
            $back = '/';
        }

        return $this->redirect($back);
    }
}
