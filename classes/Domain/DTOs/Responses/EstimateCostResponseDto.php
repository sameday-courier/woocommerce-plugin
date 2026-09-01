<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs\Responses;

final class EstimateCostResponseDto
{
    private float $cost;

    private string $currency;

    /**
     * @param float $cost
     * @param string $currency
     */
    public function __construct(float $cost, string $currency)
    {
        $this->cost = $cost;
        $this->currency = $currency;
    }

    /**
     * @return float
     */
    public function getCost(): float
    {
        return $this->cost;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }
}
