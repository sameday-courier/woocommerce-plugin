<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\Models\CarrierAwb;

interface OrderAwbProviderInterface
{
    /**
     * @param int $orderId
     *
     * @return CarrierAwb|null
     */
    public function get(int $orderId): ?CarrierAwb;
}
