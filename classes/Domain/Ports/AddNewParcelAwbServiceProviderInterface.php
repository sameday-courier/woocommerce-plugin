<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\AddNewParcelAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\AddNewParcelAwbResponseDto;

interface AddNewParcelAwbServiceProviderInterface
{
    /**
     * @param AddNewParcelAwbRequestDto $addNewParcelAwbRequestDto
     *
     * @return AddNewParcelAwbResponseDto
     */
    public function add(AddNewParcelAwbRequestDto $addNewParcelAwbRequestDto): AddNewParcelAwbResponseDto;
}
