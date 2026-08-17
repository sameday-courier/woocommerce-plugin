<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\CourierLockerDto;
use SamedayCourier\Shipping\Domain\Models\CarrierLocker;

interface LockerStoreServiceProviderInterface
{
    /**
     * @param int $samedayId
     *
     * @return CarrierLocker|null
     */
    public function getBySamedayId(int $samedayId): ?CarrierLocker;

    /**
     * @return CarrierLocker[]
     */
    public function getAll(): array;

    /**
     * @param CourierLockerDto $locker
     *
     * @return void
     */
    public function add(CourierLockerDto $locker): void;

    /**
     * @param CourierLockerDto $locker
     * @param int $localId
     *
     * @return bool
     */
    public function updateFromRemote(CourierLockerDto $locker, int $localId): bool;

    /**
     * @param int $id
     *
     * @return void
     */
    public function deleteById(int $id): void;
}
