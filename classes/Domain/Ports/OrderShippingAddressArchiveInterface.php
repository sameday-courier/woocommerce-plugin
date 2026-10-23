<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface OrderShippingAddressArchiveInterface
{
    /**
     * @param int $orderId
     *
     * @return void
     */
    public function refreshHomeDeliverySnapshot(int $orderId): void;
}
