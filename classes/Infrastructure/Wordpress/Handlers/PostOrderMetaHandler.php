<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

use WC_Order;

final class PostOrderMetaHandler
{
    /**
     * @param WC_Order $order
     * @param string $key
     * @param bool $single
     *
     * @return mixed
     */
    public static function get(WC_Order $order, string $key, bool $single = true)
    {
        return $order->get_meta($key, $single);
    }

    /**
     * @param WC_Order $order
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public static function update(WC_Order $order, string $key, $value): void
    {
        $order->update_meta_data(
            $key,
            $value
        );

        $order->save_meta_data();
    }
}
