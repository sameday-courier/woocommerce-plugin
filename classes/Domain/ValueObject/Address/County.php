<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\ValueObject\Address;

use SamedayCourier\Shipping\Domain\DTOs\CountyDto;

if (!defined('ABSPATH')) {
    exit;
}

final class County
{
    private function __construct()
    {
    }

    /**
     * @param string|null $countryCode
     * @param string|null $stateCode
     *
     * @return CountyDto
     */
    public static function tryCreate(?string $countryCode, ?string $stateCode): CountyDto
    {
        if (null === $countryCode || null === $stateCode || '' === $countryCode || '' === $stateCode) {
            return new CountyDto(null);
        }

        $name = html_entity_decode(WC()->countries->get_states()[$countryCode][$stateCode] ?? '');

        return new CountyDto('' === $name ? null : $name);
    }
}
