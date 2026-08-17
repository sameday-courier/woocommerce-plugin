<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\AddNewParcelRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\AddNewParcelResponseDto;

interface AddNewParcelServiceProviderInterface
{
    /**
     * @param AddNewParcelRequestDto $addNewParcelRequestDto
     *
     * @return AddNewParcelResponseDto
     */
    public function add(AddNewParcelRequestDto $addNewParcelRequestDto): AddNewParcelResponseDto;
}
