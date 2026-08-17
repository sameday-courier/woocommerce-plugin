<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\EditServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\EditServiceResponseDto;

interface EditServiceServiceProviderInterface
{
    /**
     * @param EditServiceRequestDto $editServiceRequestDto
     *
     * @return EditServiceResponseDto
     */
    public function edit(EditServiceRequestDto $editServiceRequestDto): EditServiceResponseDto;
}
