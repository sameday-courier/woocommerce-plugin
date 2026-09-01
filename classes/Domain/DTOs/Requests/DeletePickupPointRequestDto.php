<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Requests;

final class DeletePickupPointRequestDto
{
    private int $pickupPointId;

    /**
     * @param int $pickupPointId
     */
    public function __construct(int $pickupPointId)
    {
        $this->pickupPointId = $pickupPointId;
    }

    /**
     * @return int
     */
    public function getPickupPointId(): int
    {
        return $this->pickupPointId;
    }
}
