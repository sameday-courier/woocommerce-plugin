<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\SamedaySessionKeys;
use SamedayCourier\Shipping\Domain\Shipping\ShippingMethodCodeParser;

if (!defined('ABSPATH')) {
    exit;
}

final class WooShippingMethodProvider
{
    public static function getChosenServiceCode(): string
    {
        $chosenShippingMethods = WooSessionHandler::get(SamedaySessionKeys::CHOSEN_SHIPPING_METHODS);
        $chosenShippingMethod = is_array($chosenShippingMethods) ? ($chosenShippingMethods[0] ?? null) : null;

        if (null === $chosenShippingMethod) {
            return '';
        }

        return ShippingMethodCodeParser::parse($chosenShippingMethod);
    }
}
