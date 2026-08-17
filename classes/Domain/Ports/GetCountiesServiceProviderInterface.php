<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCountiesServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\GetCountiesServiceResponseDto;

interface GetCountiesServiceProviderInterface
{
    /**
     * @param GetCountiesServiceRequestDto $getCountiesServiceRequestDto
     *
     * @return GetCountiesServiceResponseDto
     */
    public function get(GetCountiesServiceRequestDto $getCountiesServiceRequestDto): GetCountiesServiceResponseDto;
}
