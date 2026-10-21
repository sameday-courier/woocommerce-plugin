<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Ports\ShippingOrderProviderInterface;
use WC_Order;
use WC_Order_Item_Shipping;

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

        if (!$order instanceof WC_Order) {
            return null;
        }

        $orderData = $order->get_data();
        $orderData['shipping_lines'] = [];

        foreach ($order->get_items('shipping') as $shippingLine) {
            if ($shippingLine instanceof WC_Order_Item_Shipping) {
                $orderData['shipping_lines'][] = $shippingLine->get_id();
            }
        }

        return $orderData;
    }
}
