<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Shipping\ShippingMethodCodeParser;

if (!defined('ABSPATH')) {
    exit;
}

final class WooShippingMethodProvider
{
    public static function getChosenServiceCode(): string
    {
        $chosenShippingMethod = WC()->session->get('chosen_shipping_methods')[0] ?? null;

        if (null === $chosenShippingMethod) {
            return '';
        }

        return ShippingMethodCodeParser::parse($chosenShippingMethod);
    }
}
