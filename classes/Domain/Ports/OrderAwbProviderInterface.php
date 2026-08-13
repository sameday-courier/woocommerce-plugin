<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\Models\SamedayAwb;

interface OrderAwbProviderInterface
{
    /**
     * @param int $orderId
     *
     * @return SamedayAwb|null
     */
    public function get(int $orderId): ?SamedayAwb;
}
