<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\RefreshServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\RefreshServiceResponseDto;

interface RefreshServiceServiceProviderInterface
{
    /**
     * @param RefreshServiceRequestDto $refreshServiceRequestDto
     *
     * @return RefreshServiceResponseDto
     */
    public function refresh(RefreshServiceRequestDto $refreshServiceRequestDto): RefreshServiceResponseDto;
}
