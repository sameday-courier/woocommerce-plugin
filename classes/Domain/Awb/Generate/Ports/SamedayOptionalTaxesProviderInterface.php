<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Awb\Generate\Ports;

use Sameday\Objects\Service\OptionalTaxObject;

if (!defined('ABSPATH')) {
    exit;
}

interface SamedayOptionalTaxesProviderInterface
{
    /**
     * @return OptionalTaxObject[]
     */
    public function getOptionalTaxesForService(int $samedayServiceId): array;
}
