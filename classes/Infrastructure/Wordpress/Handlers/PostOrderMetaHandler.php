<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

use WC_Order;

final class PostOrderMetaHandler
{
    /**
     * @param WC_Order $order
     * @param string $key
     *
     * @return array|mixed|string|null
     */
    public static function get(WC_Order $order, string $key)
    {
        $metaData = $order->get_meta($key);
        if ('' === $metaData || false === $metaData || null === $metaData) {
            return null;
        }

        return $metaData;
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
