<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface CarrierShippingHdAddressParserInterface
{
    /**
     * @param int $orderId
     *
     * @return array<string,
     */
    public function parse(int $orderId): ?array;
}
