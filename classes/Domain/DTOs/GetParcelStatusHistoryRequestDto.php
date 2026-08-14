<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class GetParcelStatusHistoryRequestDto
{
    private string $parcel;

    public function __construct(string $parcel)
    {
        $this->parcel = $parcel;
    }

    public function getParcel(): string
    {
        return $this->parcel;
    }
}
