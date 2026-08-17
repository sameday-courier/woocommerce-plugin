<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\RefreshPickupPointRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\RefreshPickupPointResponseDto;

interface RefreshPickupPointServiceProviderInterface
{
    /**
     * @param RefreshPickupPointRequestDto $refreshPickupPointRequestDto
     *
     * @return RefreshPickupPointResponseDto
     */
    public function refresh(RefreshPickupPointRequestDto $refreshPickupPointRequestDto): RefreshPickupPointResponseDto;
}
