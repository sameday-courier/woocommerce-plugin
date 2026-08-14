<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

use Sameday\Objects\PostAwb\ParcelObject;

final class PostAwbResponseDto
{
    private string $awbNumber;

    private float $cost;

    /**
     * @var ParcelObject[]
     */
    private array $parcels;

    /**
     * @param ParcelObject[] $parcels
     */
    public function __construct(string $awbNumber, float $cost, array $parcels)
    {
        $this->awbNumber = $awbNumber;
        $this->cost = $cost;
        $this->parcels = $parcels;
    }

    public function getAwbNumber(): string
    {
        return $this->awbNumber;
    }

    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * @return ParcelObject[]
     */
    public function getParcels(): array
    {
        return $this->parcels;
    }
}
