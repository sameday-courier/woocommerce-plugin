<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters;

if (!defined('ABSPATH')) {
    exit;
}

final class ShippingMethodFullLabelFilter extends AbstractFilter
{
    private const FILTER = 'woocommerce_cart_shipping_method_full_label';

    /**
     * @return string
     */
    public function getFilterName(): string
    {
        return self::FILTER;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 10;
    }

    /**
     * @return string[]|null
     */
    public function getParams(): ?array
    {
        return ['label', 'method'];
    }

    /**
     * @param mixed ...$args
     *
     * @return mixed
     */
    public function handle(...$args)
    {
        $label = $args[0] ?? '';
        $method = $args[1] ?? null;

        if (!is_object($method) || !method_exists($method, 'get_meta_data')) {
            return $label;
        }

        $metaData = $method->get_meta_data();

        return $metaData['currency_conversion_label'] ?? $label;
    }
}
