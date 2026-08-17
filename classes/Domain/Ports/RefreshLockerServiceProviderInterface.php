<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\RefreshLockerRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\RefreshLockerResponseDto;

interface RefreshLockerServiceProviderInterface
{
    /**
     * @param RefreshLockerRequestDto $refreshLockerRequestDto
     *
     * @return RefreshLockerResponseDto
     */
    public function refresh(RefreshLockerRequestDto $refreshLockerRequestDto): RefreshLockerResponseDto;
}
