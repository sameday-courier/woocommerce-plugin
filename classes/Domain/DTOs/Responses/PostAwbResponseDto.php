<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

use SamedayCourier\Shipping\Domain\DTOs\CarrierAwbParcelDto;

final class PostAwbResponseDto
{
    private string $awbNumber;

    private float $cost;

    /**
     * @var CarrierAwbParcelDto[]
     */
    private array $parcels;

    /**
     * @param string $awbNumber
     * @param float $cost
     * @param CarrierAwbParcelDto[] $parcels
     */
    public function __construct(string $awbNumber, float $cost, array $parcels)
    {
        $this->awbNumber = $awbNumber;
        $this->cost = $cost;
        $this->parcels = $parcels;
    }

    /**
     * @return string
     */
    public function getAwbNumber(): string
    {
        return $this->awbNumber;
    }

    /**
     * @return float
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * @return CarrierAwbParcelDto[]
     */
    public function getParcels(): array
    {
        return $this->parcels;
    }
}
