<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

if (!defined('ABSPATH')) {
    exit;
}

interface OrderShippingAddressUpdaterInterface
{
    /**
     * @param int $orderId
     *
     * @return void
     */
    public function activateOutOfHome(int $orderId): void;

    /**
     * @param int $orderId
     *
     * @return void
     */
    public function activateHomeDelivery(int $orderId): void;
}
