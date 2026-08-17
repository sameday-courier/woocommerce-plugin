<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class PostParcelResponseDto
{
    private string $parcelAwbNumber;

    public function __construct(string $parcelAwbNumber)
    {
        $this->parcelAwbNumber = $parcelAwbNumber;
    }

    public function getParcelAwbNumber(): string
    {
        return $this->parcelAwbNumber;
    }
}
