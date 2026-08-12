<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\GenerateAwbOrderSnapshot;

if (!defined('ABSPATH')) {
    exit;
}

interface GenerateAwbOrderProviderInterface
{
    /**
     * @param int $orderId
     *
     * @return GenerateAwbOrderSnapshot|null
     */
    public function getById(int $orderId): ?GenerateAwbOrderSnapshot;
}
