<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use Sameday\Objects\PickupPoint\PickupPointObject;

final class GetPickupPointsResponseDto
{
    /**
     * @var PickupPointObject[]
     */
    private array $pickupPoints;

    private int $pages;

    /**
     * @param PickupPointObject[] $pickupPoints
     */
    public function __construct(array $pickupPoints, int $pages)
    {
        $this->pickupPoints = $pickupPoints;
        $this->pages = $pages;
    }

    /**
     * @return PickupPointObject[]
     */
    public function getPickupPoints(): array
    {
        return $this->pickupPoints;
    }

    public function getPages(): int
    {
        return $this->pages;
    }
}
