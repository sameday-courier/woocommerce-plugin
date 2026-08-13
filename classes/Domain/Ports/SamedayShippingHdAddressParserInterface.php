<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

interface SamedayShippingHdAddressParserInterface
{
    /**
     * @param int $orderId
     *
     * @return array<string, mixed>|null
     */
    public function parse(int $orderId): ?array;
}
