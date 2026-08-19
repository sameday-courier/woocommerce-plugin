<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\CourierLockerDto;
use SamedayCourier\Shipping\Domain\Models\CarrierLocker;
use SamedayCourier\Shipping\Domain\Ports\LockerStoreServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;

final class LockerStoreServiceProvider implements LockerStoreServiceProviderInterface
{
    private SamedayLockerRepository $samedayLockerRepository;

    /**
     * @param ?SamedayLockerRepository $samedayLockerRepository
     */
    public function __construct(?SamedayLockerRepository $samedayLockerRepository = null)
    {
        $this->samedayLockerRepository = $samedayLockerRepository ?? new SamedayLockerRepository();
    }

    /**
     * @param int $samedayId
     *
     * @return ?CarrierLocker
     */
    public function getBySamedayId(int $samedayId): ?CarrierLocker
    {
        return $this->samedayLockerRepository->getLockerSameday($samedayId);
    }

    /**
     * @return CarrierLocker[]
     */
    public function getAll(): array
    {
        return $this->samedayLockerRepository->getLockers();
    }

    /**
     * @param CourierLockerDto $locker
     *
     * @return void
     */
    public function add(CourierLockerDto $locker): void
    {
        $this->samedayLockerRepository->addLocker($locker);
    }

    /**
     * @param CourierLockerDto $locker
     * @param int $localId
     *
     * @return bool
     */
    public function updateFromRemote(CourierLockerDto $locker, int $localId): bool
    {
        return $this->samedayLockerRepository->updateLocker($locker, $localId);
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function deleteById(int $id): void
    {
        $this->samedayLockerRepository->deleteLocker($id);
    }
}
