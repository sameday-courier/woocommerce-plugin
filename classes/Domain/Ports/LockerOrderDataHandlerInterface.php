<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface LockerOrderDataHandlerInterface
{
    /**
     * @param int $orderId
     * @param mixed $locker
     *
     * @return void
     */
    public function add(int $orderId, $locker): void;
}
