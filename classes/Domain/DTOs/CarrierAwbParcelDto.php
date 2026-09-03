<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\DTOs;

final class CarrierAwbParcelDto
{
    private int $position;

    private string $awbNumber;

    /**
     * @param int $position
     * @param string $awbNumber
     */
    public function __construct(int $position, string $awbNumber)
    {
        $this->position = $position;
        $this->awbNumber = $awbNumber;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @return string
     */
    public function getAwbNumber(): string
    {
        return $this->awbNumber;
    }
}
