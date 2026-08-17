<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class GetCitiesServiceRequestDto
{
    private int $countyId;

    public function __construct(int $countyId)
    {
        $this->countyId = $countyId;
    }

    public function getCountyId(): int
    {
        return $this->countyId;
    }
}
