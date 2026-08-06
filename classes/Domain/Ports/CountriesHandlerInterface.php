<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

if (!defined('ABSPATH')) {
    exit;
}

interface CountriesHandlerInterface
{
    /**
     * @return array<string, string>
     */
    public function getShippingCountries(): array;

    /**
     * @return array<string, string>|null
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
