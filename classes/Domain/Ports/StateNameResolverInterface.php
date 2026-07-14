<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

if (!defined('ABSPATH')) {
    exit;
}

interface StateNameResolverInterface
{
    public function resolveNameFromCode(?string $countryCode, ?string $stateCode): ?string;
}
