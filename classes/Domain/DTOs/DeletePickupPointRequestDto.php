<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class DeletePickupPointRequestDto
{
    private int $pickupPointId;

    public function __construct(int $pickupPointId)
    {
        $this->pickupPointId = $pickupPointId;
    }

    public function getPickupPointId(): int
    {
        return $this->pickupPointId;
    }
}
