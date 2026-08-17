<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\Responses\GetParcelStatusHistoryResponseDto;
use SamedayCourier\Shipping\Domain\Models\CarrierPackage;

interface PackageHistoryStoreServiceProviderInterface
{
    /**
     * @param int $orderId
     *
     * @return CarrierPackage[]
     */
    public function getForOrder(int $orderId): array;

    /**
     * @param int $orderId
     *
     * @return void
     */
    public function deleteByOrder(int $orderId): void;

    /**
     * @param int $orderId
     * @param string $parcelAwbNumber
     * @param GetParcelStatusHistoryResponseDto $parcelStatus
     *
     * @return void
     */
    public function refresh(
        int $orderId,
        string $parcelAwbNumber,
        GetParcelStatusHistoryResponseDto $parcelStatus
    ): void;
}
