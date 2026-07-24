<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

if (!defined('ABSPATH')) {
    exit;
}

final class WooCountriesHandler
{
    /**
     * @return array<string, string>
     */
    public static function getShippingCountries(): array
    {
        return WooHandler::getWC()->countries->get_shipping_countries();
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function getAllStates(): array
    {
        return WooHandler::getWC()->countries->get_states();
    }

    /**
     * @return array<string, string>|null
     */
    public static function getStatesForCountry(string $countryCode): ?array
    {
        $states = self::getAllStates()[$countryCode] ?? null;

        return is_array($states) ? $states : null;
    }

    /**
     * @param string $countryCode
     * @param string $stateCode
     *
     * @return string
     */
    public static function getStateName(string $countryCode, string $stateCode): string
    {
        $states = self::getStatesForCountry($countryCode);

        if (null === $states) {
            return '';
        }

        return ($states[$stateCode] ?? '');
    }
}
