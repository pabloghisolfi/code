<?php

declare(strict_types=1);

namespace Daris\Cart;

use Daris\Catalog\CatalogRepository;
use Daris\Catalog\Product;
use Daris\Config\Config;
use Daris\Http\Session;

/**
 * Panier stocké en session. Seuls les identifiants et quantités sont
 * conservés : les prix sont toujours relus depuis le catalogue.
 */
final class Cart
{
    private const SESSION_KEY = 'cart';
    private const PROMO_KEY = 'cart_promo';
    private const OPTIONS_KEY = 'cart_options';

    /** @var array<string, CartItem>|null */
    private ?array $items = null;

    public function __construct(
        private readonly Session $session,
        private readonly CatalogRepository $catalog,
        private readonly Config $config
    ) {
    }

    /**
     * @return list<CartItem>
     */
    public function items(): array
    {
        return array_values($this->load());
    }

    public function isEmpty(): bool
    {
        return $this->load() === [];
    }

    public function count(): int
    {
        return array_sum(array_map(static fn (CartItem $i): int => $i->quantity(), $this->load()));
    }

    public function add(Product $product, ?string $variantId, int $quantity = 1): CartItem
    {
        $items = $this->load();
        $variant = $variantId !== null ? $product->variant($variantId) : null;
        $key = CartItem::keyFor($product->id(), $variant?->id());

        if (isset($items[$key])) {
            $items[$key]->setQuantity($items[$key]->quantity() + $quantity);
        } else {
            $items[$key] = new CartItem($product, $variant, $quantity);
        }

        $this->save($items);

        return $items[$key];
    }

    public function update(string $key, int $quantity): void
    {
        $items = $this->load();
        if (!isset($items[$key])) {
            return;
        }
        if ($quantity <= 0) {
            unset($items[$key]);
        } else {
            $items[$key]->setQuantity($quantity);
        }
        $this->save($items);
    }

    public function remove(string $key): void
    {
        $items = $this->load();
        unset($items[$key]);
        $this->save($items);
    }

    public function clear(): void
    {
        $this->items = [];
        $this->session->remove(self::SESSION_KEY);
        $this->session->remove(self::PROMO_KEY);
        $this->session->remove(self::OPTIONS_KEY);
    }

    public function applyPromo(string $code): ?PromoCode
    {
        /** @var array<string, array{type: string, value: int, label: string}> $definitions */
        $definitions = $this->config->array('shop.promo_codes');
        $promo = PromoCode::find($code, $definitions);
        if ($promo !== null) {
            $this->session->set(self::PROMO_KEY, $promo->code());
        }

        return $promo;
    }

    public function removePromo(): void
    {
        $this->session->remove(self::PROMO_KEY);
    }

    public function promo(): ?PromoCode
    {
        /** @var array<string, array{type: string, value: int, label: string}> $definitions */
        $definitions = $this->config->array('shop.promo_codes');

        return PromoCode::find($this->session->getString(self::PROMO_KEY), $definitions);
    }

    public function setOptions(string $shippingMethod, bool $giftWrap): void
    {
        $methods = $this->config->array('shop.shipping_methods');
        $this->session->set(self::OPTIONS_KEY, [
            'shipping' => isset($methods[$shippingMethod]) ? $shippingMethod : 'economy',
            'gift_wrap' => $giftWrap,
        ]);
    }

    public function shippingMethod(): string
    {
        $options = $this->session->getArray(self::OPTIONS_KEY);
        $method = is_string($options['shipping'] ?? null) ? $options['shipping'] : 'economy';
        $methods = $this->config->array('shop.shipping_methods');

        return isset($methods[$method]) ? $method : 'economy';
    }

    public function giftWrap(): bool
    {
        $options = $this->session->getArray(self::OPTIONS_KEY);

        return (bool) ($options['gift_wrap'] ?? false);
    }

    public function subtotal(): int
    {
        return array_sum(array_map(static fn (CartItem $i): int => $i->total(), $this->load()));
    }

    /**
     * Montant restant pour obtenir la livraison offerte (0 si déjà atteinte).
     */
    public function remainingForFreeShipping(): int
    {
        return max(0, $this->config->int('shop.free_shipping_from') - $this->subtotal());
    }

    public function totals(): CartTotals
    {
        $subtotal = $this->subtotal();
        $promo = $this->promo();
        $discount = $promo?->discountOn($subtotal) ?? 0;

        /** @var array<string, array{label: string, delay: string, price: int}> $methods */
        $methods = $this->config->array('shop.shipping_methods');
        $method = $methods[$this->shippingMethod()] ?? ['label' => 'Livraison', 'delay' => '', 'price' => 0];

        $shipping = $method['price'];
        $freeFrom = $this->config->int('shop.free_shipping_from');
        if ($subtotal === 0 || ($promo !== null && $promo->freeShipping()) || ($this->shippingMethod() === 'economy' && $subtotal >= $freeFrom)) {
            $shipping = 0;
        }

        $giftWrap = $this->giftWrap() && $subtotal > 0 ? $this->config->int('shop.gift_wrap_price') : 0;

        return new CartTotals(
            $subtotal,
            $discount,
            $shipping,
            $giftWrap,
            $this->config->float('shop.vat_rate'),
            $method['label'],
            $promo?->label()
        );
    }

    /**
     * @return array<string, CartItem>
     */
    private function load(): array
    {
        if ($this->items !== null) {
            return $this->items;
        }

        $this->items = [];
        foreach ($this->session->getArray(self::SESSION_KEY) as $row) {
            if (!is_array($row) || !isset($row['product_id'], $row['quantity'])) {
                continue;
            }
            $product = $this->catalog->byId((int) $row['product_id']);
            if ($product === null) {
                continue;
            }
            $variantId = is_string($row['variant'] ?? null) ? $row['variant'] : null;
            $variant = $variantId !== null ? $product->variant($variantId) : null;
            $item = new CartItem($product, $variant, (int) $row['quantity']);
            $this->items[$item->key()] = $item;
        }

        return $this->items;
    }

    /**
     * @param array<string, CartItem> $items
     */
    private function save(array $items): void
    {
        $this->items = $items;
        $this->session->set(self::SESSION_KEY, array_values(array_map(
            static fn (CartItem $item): array => [
                'product_id' => $item->product()->id(),
                'variant' => $item->variant()?->id(),
                'quantity' => $item->quantity(),
            ],
            $items
        )));
    }
}
