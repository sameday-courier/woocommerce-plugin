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
    public function ensureHomeDeliverySnapshot(int $orderId): void;

    /**
     * @param int $orderId
     *
     * @return void
     */
    public function updateHomeDeliverySnapshot(int $orderId): void;
}
