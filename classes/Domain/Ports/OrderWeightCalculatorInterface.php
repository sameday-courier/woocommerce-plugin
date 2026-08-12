<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

if (!defined('ABSPATH')) {
    exit;
}

interface OrderWeightCalculatorInterface
{
    /**
     * Canonical package-dimensions payload for GenerateAwbItem (weight-only for bulk).
     *
     * @param int $orderId
     *
     * @return array<int, array{weight: float}>
     */
    public function toPackageDimensions(int $orderId): array;
}
