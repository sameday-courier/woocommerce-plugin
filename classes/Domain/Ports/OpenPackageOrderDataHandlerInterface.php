<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface OpenPackageOrderDataHandlerInterface
{
    /**
     * @param int $orderId
     *
     * @return void
     */
    public function saveFromSession(int $orderId): void;

    /**
     * @param int $orderId
     *
     * @return bool
     */
    public function isEnabled(int $orderId): bool;
}
