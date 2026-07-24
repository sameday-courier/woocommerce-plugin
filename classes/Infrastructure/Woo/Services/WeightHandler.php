<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

if (!defined('ABSPATH')) {
    exit;
}

final class WeightHandler
{
    private const KEY = 'woocommerce_weight_unit';

    private const DEFAULT_UNIT = 'kg';

    /**
     * @param float $weight
     * @return float
     */
    public static function convert(float $weight): float
    {
        $weightUnit = OptionsHandler::getOption(self::KEY, self::DEFAULT_UNIT);

        switch ($weightUnit) {
            case 'g':
                return ($weight / 1000);
            case 'lbs':
                return ($weight * 0.45);
            case 'oz':
                return ($weight * 0.028);
            default:
                return $weight;
        }
    }
}
