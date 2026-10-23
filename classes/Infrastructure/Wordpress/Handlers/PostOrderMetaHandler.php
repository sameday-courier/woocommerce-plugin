<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers;

use RuntimeException;
use WC_Order;

final class PostOrderMetaHandler
{
    /**
     * @param int $postId
     * @param string $key
     * @param bool $single
     *
     * @return mixed
     */
    public static function get(int $postId, string $key, bool $single = true)
    {
        try {
            $order = self::getOrder($postId);
        } catch (RuntimeException $exception) {
            return false;
        }

        if ($order->meta_exists($key)) {
            return $order->get_meta($key, $single);
        }

        return false;
    }

    /**
     * @param int $postId
     * @param string $key
     * @param mixed $value
     * @param bool $unique
     *
     * @return void
     */
    public static function update(int $postId, string $key, $value, bool $unique = false): void
    {
        try {
            $order = self::getOrder($postId);
        } catch (RuntimeException $exception) {
            return;
        }

        $order->update_meta_data(
            $key,
            $value,
            $unique
        );

        $order->save_meta_data();
    }

    /**
     * @throws RuntimeException
     */
    private static function getOrder(int $postId): WC_Order
    {
        $order = wc_get_order($postId);
        if (false === $order instanceof WC_Order) {
            throw new RuntimeException("Order not found");
        }

        return $order;
    }
}
