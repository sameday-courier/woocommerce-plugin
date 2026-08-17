<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCitiesServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\GetCitiesServiceResponseDto;

interface GetCitiesServiceProviderInterface
{
    /**
     * @param GetCitiesServiceRequestDto $getCitiesServiceRequestDto
     *
     * @return GetCitiesServiceResponseDto
     */
    public function get(GetCitiesServiceRequestDto $getCitiesServiceRequestDto): GetCitiesServiceResponseDto;
}
