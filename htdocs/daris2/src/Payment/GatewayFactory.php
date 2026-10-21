<?php

declare(strict_types=1);

namespace Daris\Payment;

use Daris\Config\Config;
use Daris\Support\Url;

/**
 * Passerelle boutique (Stripe optionnel via .env, sans simulation de carte).
 */
final class GatewayFactory
{
    private function __construct()
    {
    }

    public static function make(Config $config, Url $url): PaymentGatewayInterface
    {
        return new ShopGateway($config, $url);
    }
}
