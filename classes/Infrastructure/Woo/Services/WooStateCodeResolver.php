<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Woo\Services;

use SamedayCourier\Shipping\Domain\Text\RomanianDiacriticsNormalizer;

if (!defined('ABSPATH')) {
    exit;
}

final class WooStateCodeResolver
{
    public static function resolveNameFromCode(?string $countryCode, ?string $stateCode): ?string
    {
        if (null === $countryCode || null === $stateCode || '' === $countryCode || '' === $stateCode) {
            return null;
        }

        $name = html_entity_decode(WooCountriesHandler::getStateName($countryCode, $stateCode));

        return '' === $name ? null : $name;
    }

    public static function resolveFromName(string $countryCode, string $stateName): string
    {
        if ('' === $countryCode || '' === $stateName) {
            return '';
        }

        $states = WooCountriesHandler::getStatesForCountry($countryCode);

        if (null === $states) {
            return '';
        }

        foreach ($states as $code => $name) {
            if (RomanianDiacriticsNormalizer::normalize($name) === RomanianDiacriticsNormalizer::normalize($stateName)) {
                return (string) $code;
            }
        }

        return '';
    }
}
