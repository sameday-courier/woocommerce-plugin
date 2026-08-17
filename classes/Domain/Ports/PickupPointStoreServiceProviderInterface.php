<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\CourierPickupPointDto;
use SamedayCourier\Shipping\Domain\Models\CarrierPickupPoint;

interface PickupPointStoreServiceProviderInterface
{
    /**
     * @param int $samedayId
     *
     * @return CarrierPickupPoint|null
     */
    public function getBySamedayId(int $samedayId): ?CarrierPickupPoint;

    /**
     * @return CarrierPickupPoint[]
     */
    public function getAll(): array;

    /**
     * @param CourierPickupPointDto $pickupPoint
     *
     * @return void
     */
    public function add(CourierPickupPointDto $pickupPoint): void;

    /**
     * @param CourierPickupPointDto $pickupPoint
     * @param int $localId
     *
     * @return bool
     */
    public function updateFromRemote(CourierPickupPointDto $pickupPoint, int $localId): bool;

    /**
     * @param int $id
     *
     * @return void
     */
    public function deleteById(int $id): void;
}
