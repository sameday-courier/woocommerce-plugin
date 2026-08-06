<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\Models\SamedayAwb;

if (!defined('ABSPATH')) {
    exit;
}

interface OrderAwbProviderInterface
{
    /**
     * @param int $orderId
     *
     * @return SamedayAwb|null
     */
    public function get(int $orderId): ?SamedayAwb;
}
