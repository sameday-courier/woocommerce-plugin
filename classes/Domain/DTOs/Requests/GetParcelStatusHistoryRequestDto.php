<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class GetParcelStatusHistoryRequestDto
{
    private string $parcel;

    /**
     * @param string $parcel
     */
    public function __construct(string $parcel)
    {
        $this->parcel = $parcel;
    }

    /**
     * @return string
     */
    public function getParcel(): string
    {
        return $this->parcel;
    }
}
