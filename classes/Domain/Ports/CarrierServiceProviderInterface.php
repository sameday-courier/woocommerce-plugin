<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use Sameday\Objects\Service\OptionalTaxObject;
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
     * @return OptionalTaxObject[]
     */
    public function getServiceIdOptionalTaxes(int $samedayServiceId): array;
}
