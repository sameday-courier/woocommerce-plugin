<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\AddNewPickupPointServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\AddNewPickupPointServiceResponseDto;

interface AddNewPickupPointServiceProviderInterface
{
    /**
     * @param AddNewPickupPointServiceRequestDto $addNewPickupPointServiceRequestDto
     *
     * @return AddNewPickupPointServiceResponseDto
     */
    public function add(
        AddNewPickupPointServiceRequestDto $addNewPickupPointServiceRequestDto
    ): AddNewPickupPointServiceResponseDto;
}
