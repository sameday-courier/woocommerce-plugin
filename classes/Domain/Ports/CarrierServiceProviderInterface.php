<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\CarrierOptionalTaxDto;
use SamedayCourier\Shipping\Domain\Models\CarrierService;

interface CarrierServiceProviderInterface
{
    /**
     * @return CarrierService[]
     */
    public function getAvailableServices(): array;

    /**
     * @param int $samedayServiceId
     *
     * @return CarrierOptionalTaxDto[]
     */
    public function getServiceIdOptionalTaxes(int $samedayServiceId): array;
}
