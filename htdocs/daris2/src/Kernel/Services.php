<?php

declare(strict_types=1);

namespace Daris\Kernel;

use Daris\Assistant\FaqRepository;
use Daris\Assistant\StyleAdvisor;
use Daris\Cart\Cart;
use Daris\Catalog\CatalogRepository;
use Daris\Catalog\LookRepository;
use Daris\Config\Config;
use Daris\Contact\ContactRepository;
use Daris\Database\Connection;
use Daris\Http\Csrf;
use Daris\Http\RateLimiter;
use Daris\Http\Request;
use Daris\Http\Session;
use Daris\Mail\Mailer;
use Daris\Order\CheckoutService;
use Daris\Order\OrderRepository;
use Daris\Payment\GatewayFactory;
use Daris\Support\Url;
use Daris\View\View;

/**
 * Conteneur de services typé : chaque dépendance est construite une seule
 * fois, à la demande. Les contrôleurs reçoivent cette instance.
 */
final class Services
{
    /** @var array<string, object> */
    private array $instances = [];

    public function __construct(
        private readonly Config $config,
        private readonly Request $request,
        private readonly Session $session
    ) {
    }

    public function config(): Config
    {
        return $this->config;
    }

    public function request(): Request
    {
        return $this->request;
    }

    public function session(): Session
    {
        return $this->session;
    }

    public function csrf(): Csrf
    {
        return $this->once(Csrf::class, fn (): Csrf => new Csrf($this->session));
    }

    public function url(): Url
    {
        return $this->once(Url::class, function (): Url {
            $siteUrl = $this->config->string('app.url');
            if ($siteUrl === '' && $this->request->host() !== '') {
                $siteUrl = ($this->request->isHttps() ? 'https' : 'http') . '://' . $this->request->host();
            }

            return new Url($this->config->string('app.base_path'), $siteUrl, $this->config->string('paths.assets'));
        });
    }

    public function view(): View
    {
        return $this->once(View::class, fn (): View => new View($this->config->string('paths.templates'), $this->url(), $this->csrf()));
    }

    public function catalog(): CatalogRepository
    {
        return $this->once(CatalogRepository::class, fn (): CatalogRepository => new CatalogRepository($this->config->string('paths.data')));
    }

    public function looks(): LookRepository
    {
        return $this->once(LookRepository::class, fn (): LookRepository => new LookRepository($this->config->string('paths.data')));
    }

    public function faq(): FaqRepository
    {
        return $this->once(FaqRepository::class, fn (): FaqRepository => new FaqRepository($this->config->string('paths.data')));
    }

    public function advisor(): StyleAdvisor
    {
        return $this->once(StyleAdvisor::class, fn (): StyleAdvisor => new StyleAdvisor($this->config->string('paths.data')));
    }

    public function cart(): Cart
    {
        return $this->once(Cart::class, fn (): Cart => new Cart($this->session, $this->catalog(), $this->config));
    }

    public function database(): Connection
    {
        return $this->once(Connection::class, fn (): Connection => new Connection(
            $this->config->string('paths.var') . '/' . basename($this->config->string('database.file', 'daris.sqlite'))
        ));
    }

    public function orders(): OrderRepository
    {
        return $this->once(OrderRepository::class, fn (): OrderRepository => new OrderRepository($this->database(), $this->secret()));
    }

    public function contacts(): ContactRepository
    {
        return $this->once(ContactRepository::class, fn (): ContactRepository => new ContactRepository($this->database(), $this->secret()));
    }

    public function mailer(): Mailer
    {
        return $this->once(Mailer::class, fn (): Mailer => new Mailer(
            $this->config->bool('mail.enabled'),
            $this->config->string('mail.from'),
            $this->config->string('paths.var') . '/mail'
        ));
    }

    public function rateLimiter(): RateLimiter
    {
        return $this->once(RateLimiter::class, fn (): RateLimiter => new RateLimiter(
            $this->config->string('paths.var') . '/ratelimit',
            $this->config->int('security.rate_limit_attempts', 5),
            $this->config->int('security.rate_limit_window', 600)
        ));
    }

    public function checkout(): CheckoutService
    {
        return $this->once(CheckoutService::class, fn (): CheckoutService => new CheckoutService(
            $this->cart(),
            $this->orders(),
            GatewayFactory::make($this->config->string('payment.provider', 'none')),
            $this->mailer(),
            $this->view(),
            $this->url(),
            $this->config
        ));
    }

    /**
     * Secret applicatif : celui du .env, sinon un secret local généré une fois
     * et conservé dans var/ (jamais versionné) pour que l'installation
     * fonctionne même sans configuration.
     */
    public function secret(): string
    {
        $secret = $this->config->string('app.secret');
        if ($secret !== '' && !str_starts_with($secret, 'changez-moi')) {
            return $secret;
        }

        $file = $this->config->string('paths.var') . '/.secret';
        if (is_file($file)) {
            $stored = trim((string) file_get_contents($file));
            if ($stored !== '') {
                return $stored;
            }
        }

        $generated = bin2hex(random_bytes(32));
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0750, true);
        }
        file_put_contents($file, $generated, LOCK_EX);

        return $generated;
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @param callable(): T   $factory
     * @return T
     */
    private function once(string $id, callable $factory): object
    {
        if (!isset($this->instances[$id])) {
            $this->instances[$id] = $factory();
        }

        /** @var T $instance */
        $instance = $this->instances[$id];

        return $instance;
    }
}
