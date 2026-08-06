<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\ShippingOrderProviderInterface;

if (!defined('ABSPATH')) {
    exit;
}

final class WooShippingHandler implements ShippingOrderProviderInterface
{
    /**
     * @param int $id
     *
     * @return array|null
     */
    public function getShippingOrderById(int $id): ?array
    {
        $order = wc_get_order($id);

        if (empty($order)) {
            return null;
        }

        return $order->get_data();
    }
}
