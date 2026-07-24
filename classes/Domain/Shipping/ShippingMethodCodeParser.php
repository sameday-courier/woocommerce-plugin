<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Shipping;

if (!defined('ABSPATH')) {
    exit;
}

final class ShippingMethodCodeParser
{
    /**
     * @param string $shippingMethodInput
     * @return string
     */
    public static function parse(string $shippingMethodInput): string
    {
        $serviceCode = explode(':', $shippingMethodInput, 3);

        return $serviceCode[2] ?? '';
    }
}
