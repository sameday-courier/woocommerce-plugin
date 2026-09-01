<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface CountriesHandlerInterface
{
    /**
     * @return array<string,
     */
    public function getShippingCountries(): array;

    /**
     * @param string $countryCode
     *
     * @return array<string,
     */
    public function getStatesForCountry(string $countryCode): ?array;

    /**
     * @param string $countryCode
     * @param string $stateCode
     *
     * @return string
     */
    public function getStateName(string $countryCode, string $stateCode): string;
}
