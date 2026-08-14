<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class RemoveAwbRequestDto
{
    private string $awb;

    public function __construct(string $awb)
    {
        $this->awb = $awb;
    }

    public function getAwb(): string
    {
        return $this->awb;
    }
}
