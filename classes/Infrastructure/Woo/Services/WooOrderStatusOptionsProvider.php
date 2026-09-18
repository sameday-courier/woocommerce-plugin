<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

final class WooOrderStatusOptionsProvider
{
    /**
     * @param string|null $emptyOptionLabel
     *
     * @return array
     */
    public static function getSelectOptions(?string $emptyOptionLabel = null): array
    {
        $options = [];
        if (null !== $emptyOptionLabel) {
            $options[''] = $emptyOptionLabel;
        }

        if (!function_exists('wc_get_order_statuses')) {
            return $options;
        }

        foreach (wc_get_order_statuses() as $status => $label) {
            $options[(string) $status] = (string) $label;
        }

        return $options;
    }
}
