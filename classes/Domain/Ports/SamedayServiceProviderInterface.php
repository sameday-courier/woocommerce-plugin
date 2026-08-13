<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use Sameday\Objects\Service\OptionalTaxObject;
use SamedayCourier\Shipping\Domain\Models\SamedayService;

interface SamedayServiceProviderInterface
{
    /**
     * @return SamedayService[]
     */
    public function getAvailableServices(): array;

    /**
     * @param int $samedayServiceId
     *
     * @return OptionalTaxObject[]
     */
    public function getServiceIdOptionalTaxes(int $samedayServiceId): array;
}
