<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface WeightConverterInterface
{
    /**
     * @param float $weight
     *
     * @return float
     */
    public function convert(float $weight): float;
}
