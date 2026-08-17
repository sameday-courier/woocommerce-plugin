<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\GenerateAwbOrderSnapshotDto;

interface GenerateAwbOrderProviderInterface
{
    /**
     * @param int $orderId
     *
     * @return GenerateAwbOrderSnapshotDto|null
     */
    public function getById(int $orderId): ?GenerateAwbOrderSnapshotDto;
}
