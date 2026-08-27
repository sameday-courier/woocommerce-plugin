<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Requests\PostAwbGenerationRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\PostAwbGenerationResponseDto;

interface PostAwbGenerationServiceProviderInterface
{
    /**
     * @param PostAwbGenerationRequestDto $postAwbGenerationRequestDto
     * @param CourierServiceProviderInterface $courier
     *
     * @return PostAwbGenerationResponseDto
     */
    public function apply(
        PostAwbGenerationRequestDto $postAwbGenerationRequestDto,
        CourierServiceProviderInterface $courier
    ): PostAwbGenerationResponseDto;
}
