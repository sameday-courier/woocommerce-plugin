<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

if (!defined('ABSPATH')) {
    exit;
}

interface StateCodeResolverInterface
{
    /**
     * @param string|null $countryCode
     * @param string|null $stateCode
     *
     * @return string|null
     */
    public function resolveNameFromCode(?string $countryCode, ?string $stateCode): ?string;

    /**
     * @param string $countryCode
     * @param string $stateName
     *
     * @return string
     */
    public function resolveFromName(string $countryCode, string $stateName): string;
}
