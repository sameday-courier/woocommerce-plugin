<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\OrderShippingChangesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\OrderShippingChangesResponseDto;

interface OrderShippingChangesServiceProviderInterface
{
    /**
     * @param OrderShippingChangesRequestDto $request
     *
     * @return OrderShippingChangesResponseDto
     */
    public function apply(OrderShippingChangesRequestDto $request): OrderShippingChangesResponseDto;
}
