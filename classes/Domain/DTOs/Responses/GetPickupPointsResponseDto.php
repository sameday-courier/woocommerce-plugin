<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

use SamedayCourier\Shipping\Domain\DTOs\CourierPickupPointDto;

final class GetPickupPointsResponseDto
{
    /**
     * @var CourierPickupPointDto[]
     */
    private array $pickupPoints;

    private int $pages;

    /**
     * @param CourierPickupPointDto[] $pickupPoints
     * @param int $pages
     */
    public function __construct(array $pickupPoints, int $pages)
    {
        $this->pickupPoints = $pickupPoints;
        $this->pages = $pages;
    }

    /**
     * @return CourierPickupPointDto[]
     */
    public function getPickupPoints(): array
    {
        return $this->pickupPoints;
    }

    /**
     * @return int
     */
    public function getPages(): int
    {
        return $this->pages;
    }
}
