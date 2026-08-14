<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\PostAwbGenerationRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\PostAwbGenerationResponseDto;
use SamedayCourier\Shipping\Domain\CarrierServiceRules;

interface PostAwbGenerationServiceProviderInterface
{
    /**
     * @param PostAwbGenerationRequestDto $postAwbGenerationRequestDto
     * @param CarrierServiceRules $rules
     * @param CourierServiceProviderInterface $courier
     * @return PostAwbGenerationResponseDto
     */
    public function apply(
        PostAwbGenerationRequestDto $postAwbGenerationRequestDto,
        CarrierServiceRules $rules,
        CourierServiceProviderInterface $courier
    ): PostAwbGenerationResponseDto;
}
