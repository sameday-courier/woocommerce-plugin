<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

if (!defined('ABSPATH')) {
    exit;
}

class WcHandler
{
    public static function getPlatformVersion(): string
    {
        return WC()->version;
    }

    /**
     * @return array
     */
    public static function getShippingCountries(): array
    {
        return WC()->countries->get_shipping_countries();
    }

    /**
     * @param int $id
     *
     * @return array|null
     */
    public static function getShippingOrderById(int $id): ?array
    {
        $order = wc_get_order($id);

        if (empty($order)) {
            return null;
        }

        return $order->get_data();
    }
}
