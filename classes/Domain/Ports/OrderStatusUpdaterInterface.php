<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface OrderStatusUpdaterInterface
{
    /**
     * @param int $orderId
     * @param string $status
     * @param string $note
     *
     * @return bool
     */
    public function update(int $orderId, string $status, string $note = ''): bool;
}
