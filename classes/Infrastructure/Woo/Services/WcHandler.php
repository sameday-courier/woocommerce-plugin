<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use WooCommerce;

if (!defined('ABSPATH')) {
    exit;
}

class WcHandler
{
    /**
     * @return WooCommerce
     */
    public static function getWC(): WooCommerce
    {
        return WC();
    }

    /**
     * @return string
     */
    public static function getPlatformVersion(): string
    {
        return self::getWC()->version;
    }
}
