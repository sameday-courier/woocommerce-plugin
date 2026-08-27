<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\Models\CarrierAwb;

interface OrderAwbStoreServiceProviderInterface
{
    /**
     * @param int $orderId
     *
     * @return CarrierAwb|null
     */
    public function getByOrderId(int $orderId): ?CarrierAwb;

    /**
     * @param int $orderId
     * @param string $awbNumber
     * @param float $awbCost
     * @param array<int, array{position: int, awbNumber: string}> $parcels
     *
     * @return bool
     */
    public function save(int $orderId, string $awbNumber, float $awbCost, array $parcels): bool;

    /**
     * @param CarrierAwb $awb
     *
     * @return int
     */
    public function nextPosition(CarrierAwb $awb): int;

    /**
     * @param CarrierAwb $awb
     * @param int $position
     * @param string $parcelAwbNumber
     *
     * @return bool
     */
    public function appendParcel(CarrierAwb $awb, int $position, string $parcelAwbNumber): bool;

    /**
     * @param CarrierAwb $awb
     *
     * @return string[]
     */
    public function parcelAwbNumbers(CarrierAwb $awb): array;
}
