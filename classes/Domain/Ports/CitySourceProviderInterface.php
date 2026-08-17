<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\CitySourceDto;

interface CitySourceProviderInterface
{
    /**
     * @return CitySourceDto[]|null
     */
    public function readCities(): ?array;
}
