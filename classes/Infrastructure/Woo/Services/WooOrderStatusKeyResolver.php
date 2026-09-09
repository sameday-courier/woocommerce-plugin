<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

final class WooOrderStatusKeyResolver
{
    /**
     * @param string $status
     *
     * @return string|null
     */
    public static function resolve(string $status): ?string
    {
        if (!function_exists('wc_get_order_statuses')) {
            return null;
        }

        $status = trim($status);
        if ('' === $status) {
            return null;
        }

        $availableStatuses = wc_get_order_statuses();

        if (array_key_exists($status, $availableStatuses)) {
            return $status;
        }

        $prefixedStatus = 0 === strpos($status, 'wc-') ? $status : 'wc-' . $status;
        if (array_key_exists($prefixedStatus, $availableStatuses)) {
            return $prefixedStatus;
        }

        return null;
    }
}
