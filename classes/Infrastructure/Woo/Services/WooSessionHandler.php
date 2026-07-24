<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

if (!defined('ABSPATH')) {
    exit;
}

final class WooSessionHandler
{
    /**
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        return WC()->session->get($key, $default);
    }

    /**
     * @param mixed $value
     */
    public static function set(string $key, $value): void
    {
        WC()->session->set($key, $value);
    }
}
