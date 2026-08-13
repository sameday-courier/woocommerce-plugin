<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface CityPostalCodeProviderInterface
{
    /**
     * @param string $countyCode
     * @param string $countryCode
     *
     * @return string|null
     */
    public function getPostalForSpecificCounty(string $countyCode, string $countryCode): ?string;
}
