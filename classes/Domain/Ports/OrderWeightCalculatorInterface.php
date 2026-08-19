<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface OrderWeightCalculatorInterface
{
    /**
     * Canonical package-dimensions payload for GenerateAwbItem (weight-only for bulk).
     *
     * @param int $orderId
     *
     * @return array<int,
     */
    public function toPackageDimensions(int $orderId): array;
}
