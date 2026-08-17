<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\DeletePickupPointServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\DeletePickupPointServiceResponseDto;

interface DeletePickupPointServiceProviderInterface
{
    /**
     * @param DeletePickupPointServiceRequestDto $deletePickupPointServiceRequestDto
     *
     * @return DeletePickupPointServiceResponseDto
     */
    public function delete(
        DeletePickupPointServiceRequestDto $deletePickupPointServiceRequestDto
    ): DeletePickupPointServiceResponseDto;
}
