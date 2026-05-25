<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

if (!defined('ABSPATH')) {
    exit;
}

class WcHandler
{
    /**
     * @return array
     */
    public static function getShippingCountries(): array
    {
        return WC()->countries->get_shipping_countries();
    }
}
