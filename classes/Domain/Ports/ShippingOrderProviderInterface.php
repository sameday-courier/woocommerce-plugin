<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface ShippingOrderProviderInterface
{
    /**
     * @param int $id
     *
     * @return array|null
     */
    public function getShippingOrderById(int $id): ?array;
}
