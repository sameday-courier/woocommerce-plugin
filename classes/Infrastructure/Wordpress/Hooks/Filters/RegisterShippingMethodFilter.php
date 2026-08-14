<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Hooks\Filters;

use SamedayCourier\Shipping\Infrastructure\Woo\Shipping\Method\SamedayCourier;
use SamedayCourier\Shipping\Domain\CarrierConstants;

final class RegisterShippingMethodFilter extends AbstractFilter
{
    private const FILTER = 'woocommerce_shipping_methods';

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
        return ['methods'];
    }

    /**
     * @param mixed ...$args
     *
     * @return array
     */
    public function handle(...$args): array
    {
        $methods = $args[0] ?? [];
        if (!is_array($methods)) {
            $methods = [];
        }

        $methods[CarrierConstants::PLUGIN_NAME] = SamedayCourier::class;

        return $methods;
    }
}
