<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\SessionHandlerInterface;
use SamedayCourier\Shipping\Domain\Ports\WooCommerceHandlerInterface;

if (!defined('ABSPATH')) {
    exit;
}

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
        return $this->wooCommerceHandler->getWC()->session->get($key, $default);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->wooCommerceHandler->getWC()->session->set($key, $value);
    }
}
