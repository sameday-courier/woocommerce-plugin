<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\CitiesRefreshRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\CitiesRefreshResponseDto;

interface CitiesServiceProviderInterface
{
    /**
     * @param CitiesRefreshRequestDto $citiesRefreshRequestDto
     *
     * @return CitiesRefreshResponseDto
     */
    public function refresh(CitiesRefreshRequestDto $citiesRefreshRequestDto): CitiesRefreshResponseDto;
}
