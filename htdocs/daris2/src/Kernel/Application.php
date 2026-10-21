<?php

declare(strict_types=1);

namespace Daris\Kernel;

use Daris\Config\Config;
use Daris\Controller\AssistantController;
use Daris\Controller\CartController;
use Daris\Controller\CatalogController;
use Daris\Controller\CheckoutController;
use Daris\Controller\ContactController;
use Daris\Controller\ErrorController;
use Daris\Controller\GuideController;
use Daris\Controller\LocaleController;
use Daris\Controller\ProductPhotoController;
use Daris\I18n\Locale;
use Daris\Controller\HomeController;
use Daris\Controller\LookController;
use Daris\Controller\NewsletterController;
use Daris\Controller\PageController;
use Daris\Controller\PricingController;
use Daris\Controller\ProductController;
use Daris\Controller\SeoController;
use Daris\Exception\HttpException;
use Daris\Http\Request;
use Daris\Http\Response;
use Daris\Http\Router;
use Daris\Http\SecurityHeaders;
use Daris\Http\Session;
use ErrorException;
use Throwable;

/**
 * Cycle de vie d'une requête : configuration, session, routage, rendu,
 * gestion des erreurs et en-têtes de sécurité.
 */
final class Application
{
    private readonly Config $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    public function run(): void
    {
        $this->configurePhp();

        $request = Request::fromGlobals($this->config->string('app.base_path'));
        $session = new Session(
            $this->config->string('security.session_name', 'daris_session'),
            $this->config->int('security.session_lifetime', 14400),
            $this->config->string('app.base_path') . '/',
            $request->isHttps()
        );

        $services = null;

        try {
            $session->start();
            $services = new Services($this->config, $request, $session);
            $this->shareLayoutData($services);
            $response = $this->routes($services)->dispatch($request);
        } catch (Throwable $exception) {
            $response = $this->handleError($exception, $services, $request, $session);
        }

        SecurityHeaders::apply($response, $request->isHttps())->send();
    }

    private function configurePhp(): void
    {
        $debug = $this->config->bool('app.debug');
        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('display_startup_errors', '0');
        ini_set('log_errors', '1');
        ini_set('expose_php', '0');
        error_reporting(E_ALL);
        mb_internal_encoding('UTF-8');
        date_default_timezone_set('Europe/Zurich');

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            if ((error_reporting() & $severity) === 0) {
                return false;
            }
            throw new ErrorException($message, 0, $severity, $file, $line);
        });
    }

    /**
     * Variables communes à tous les gabarits.
     */
    private function shareLayoutData(Services $services): void
    {
        $view = $services->view();
        $view->share('app', [
            'name' => $this->config->string('app.name'),
            'tagline' => $this->config->string('app.tagline'),
            'credit' => $this->config->string('app.credit'),
            'themeColor' => $this->config->string('app.theme_color'),
            'email' => $this->config->string('shop.email'),
            'phone' => $this->config->string('shop.phone'),
            'address' => $this->config->string('shop.address'),
            'freeShippingFrom' => $this->config->int('shop.free_shipping_from'),
        ]);
        $view->share('currentPath', $services->request()->path());
        $view->share('navCategories', $services->catalog()->categories());
        $view->share('cartCount', $services->cart()->count());
        $view->share('flash', $services->request()->isPost() ? null : $services->session()->pullFlash());
        $advisor = $services->advisor();
        $view->share('assistantGreeting', $advisor->greeting());
        $view->share('assistantSuggestions', $advisor->suggestions());
        $locale = $services->locale();
        $view->share('htmlLang', $locale === 'zh' ? 'zh-Hans' : ($locale === 'sr' ? 'sr-Latn' : $locale));
        $view->share('htmlDir', Locale::isRtl($locale) ? 'rtl' : 'ltr');
        $view->share('localeLabels', Locale::LABELS);
    }

    private function routes(Services $s): Router
    {
        $router = new Router();

        $router->get('/', fn (Request $r): Response => (new HomeController($s))->index());
        $router->get('/boutique', fn (Request $r): Response => (new CatalogController($s))->index());
        $router->get('/boutique/{category}', fn (Request $r, string $category): Response => (new CatalogController($s))->category($category));
        $router->get('/recherche', fn (Request $r): Response => (new CatalogController($s))->search());
        $router->get('/produit/{slug}', fn (Request $r, string $slug): Response => (new ProductController($s))->show($slug));
        $router->get('/produit/{slug}/photo', fn (Request $r, string $slug): Response => (new ProductPhotoController($s))->show($slug));
        $router->post('/locale', fn (Request $r): Response => (new LocaleController($s))->set());

        $router->get('/panier', fn (Request $r): Response => (new CartController($s))->index());
        $router->post('/panier/ajouter', fn (Request $r): Response => (new CartController($s))->add());
        $router->post('/panier/modifier', fn (Request $r): Response => (new CartController($s))->update());
        $router->post('/panier/supprimer', fn (Request $r): Response => (new CartController($s))->remove());
        $router->post('/panier/code', fn (Request $r): Response => (new CartController($s))->promo());

        $router->get('/commande', fn (Request $r): Response => (new CheckoutController($s))->form());
        $router->post('/commande', fn (Request $r): Response => (new CheckoutController($s))->submit());
        $router->get('/commande/suivi', fn (Request $r): Response => (new CheckoutController($s))->trackingForm());
        $router->post('/commande/suivi', fn (Request $r): Response => (new CheckoutController($s))->track());
        $router->get('/commande/{reference}', fn (Request $r, string $reference): Response => (new CheckoutController($s))->confirmation($reference));

        $router->get('/tarifs', fn (Request $r): Response => (new PricingController($s))->index());
        $router->get('/guide', fn (Request $r): Response => (new GuideController($s))->index());
        $router->get('/conseils', fn (Request $r): Response => (new GuideController($s))->index());
        $router->get('/inspirations', fn (Request $r): Response => (new LookController($s))->index());
        $router->get('/exemples', fn (Request $r): Response => (new LookController($s))->index());
        $router->get('/inspirations/{slug}', fn (Request $r, string $slug): Response => (new LookController($s))->show($slug));
        $router->get('/exemples/{slug}', fn (Request $r, string $slug): Response => (new LookController($s))->show($slug));
        $router->post('/inspirations/{slug}/panier', fn (Request $r, string $slug): Response => (new LookController($s))->addAll($slug));
        $router->post('/exemples/{slug}/panier', fn (Request $r, string $slug): Response => (new LookController($s))->addAll($slug));

        $router->get('/a-propos', fn (Request $r): Response => (new PageController($s))->about());
        $router->get('/confidentialite', fn (Request $r): Response => (new PageController($s))->privacy());
        $router->get('/cgv', fn (Request $r): Response => (new PageController($s))->terms());

        $router->get('/contact', fn (Request $r): Response => (new ContactController($s))->form());
        $router->post('/contact', fn (Request $r): Response => (new ContactController($s))->submit());
        $router->get('/conseillere', fn (Request $r): Response => (new AssistantController($s))->index());
        $router->get('/chat', fn (Request $r): Response => (new AssistantController($s))->index());
        $router->post('/conseillere/repondre', fn (Request $r): Response => (new AssistantController($s))->reply());
        $router->post('/chat/repondre', fn (Request $r): Response => (new AssistantController($s))->reply());
        $router->post('/newsletter', fn (Request $r): Response => (new NewsletterController($s))->subscribe());

        $router->get('/sitemap.xml', fn (Request $r): Response => (new SeoController($s))->sitemap());
        $router->get('/robots.txt', fn (Request $r): Response => (new SeoController($s))->robots());

        return $router;
    }

    private function handleError(Throwable $exception, ?Services $services, Request $request, Session $session): Response
    {
        $status = $exception instanceof HttpException ? $exception->getStatusCode() : 500;

        if ($status >= 500) {
            error_log(sprintf('[daris] %s: %s in %s:%d', $exception::class, $exception->getMessage(), $exception->getFile(), $exception->getLine()));
        }

        try {
            $services ??= new Services($this->config, $request, $session);
            $this->shareLayoutData($services);

            return (new ErrorController($services))->render($status, $exception, $this->config->bool('app.debug'));
        } catch (Throwable $fallback) {
            error_log('[daris] erreur pendant le rendu de la page d\'erreur : ' . $fallback->getMessage());

            return Response::html('<!DOCTYPE html><meta charset="utf-8"><title>Erreur</title><p>Une erreur est survenue. Merci de réessayer plus tard.</p>', 500);
        }
    }
}
