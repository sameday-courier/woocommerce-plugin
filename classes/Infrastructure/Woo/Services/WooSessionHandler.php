<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\WooCommerceHandlerInterface;

final class WooSessionHandler implements SessionHandlerInterface
{
    /**
     * @var WooCommerceHandlerInterface $wooCommerceHandler
     */
    private WooCommerceHandlerInterface $wooCommerceHandler;

    /**
     * @param WooCommerceHandlerInterface|null $wooCommerceHandler
     */
    public function __construct(?WooCommerceHandlerInterface $wooCommerceHandler = null)
    {
        $this->wooCommerceHandler = $wooCommerceHandler ?? new WooHandler();
    }

    /**
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $session = $this->resolveSession();
        if (null === $session) {
            return $default;
        }

        return $session->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        $session = $this->resolveSession();
        if (null === $session) {
            return;
        }

        $session->set($key, $value);
    }

    /**
     * WooCommerce only boots the session for frontend requests, so it is absent
     * on admin, cron and non-Store-API REST requests.
     *
     * @return object|null
     */
    private function resolveSession(): ?object
    {
        $woocommerce = $this->wooCommerceHandler->getWC();

        if (!isset($woocommerce->session) || !is_object($woocommerce->session)) {
            return null;
        }

        return $woocommerce->session;
    }
}
