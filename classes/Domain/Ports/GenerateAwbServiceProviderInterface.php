<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\GenerateAwbServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\GenerateAwbServiceResponseDto;

interface GenerateAwbServiceProviderInterface
{
    /**
     * @param GenerateAwbServiceRequestDto $generateAwbServiceRequestDto
     *
     * @return GenerateAwbServiceResponseDto
     */
    public function generate(GenerateAwbServiceRequestDto $generateAwbServiceRequestDto): GenerateAwbServiceResponseDto;
}
