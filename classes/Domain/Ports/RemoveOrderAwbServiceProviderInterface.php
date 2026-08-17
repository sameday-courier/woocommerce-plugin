<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveOrderAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\RemoveOrderAwbResponseDto;

interface RemoveOrderAwbServiceProviderInterface
{
    /**
     * @param RemoveOrderAwbRequestDto $removeOrderAwbRequestDto
     *
     * @return RemoveOrderAwbResponseDto
     */
    public function remove(RemoveOrderAwbRequestDto $removeOrderAwbRequestDto): RemoveOrderAwbResponseDto;
}
