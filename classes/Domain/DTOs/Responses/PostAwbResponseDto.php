<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class PostAwbResponseDto
{
    private string $awbNumber;

    private float $cost;

    /**
     * @var array<int, array{position: int, awbNumber: string}>
     */
    private array $parcels;

    /**
     * @param array<int, array{position: int, awbNumber: string}> $parcels
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
     * @return array<int, array{position: int, awbNumber: string}>
     */
    public function getParcels(): array
    {
        return $this->parcels;
    }
}
