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

    public function __construct(?SamedayLockerRepository $samedayLockerRepository = null)
    {
        $this->samedayLockerRepository = $samedayLockerRepository ?? new SamedayLockerRepository();
    }

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

    public function add(CourierLockerDto $locker): void
    {
        $this->samedayLockerRepository->addLocker($locker);
    }

    public function updateFromRemote(CourierLockerDto $locker, int $localId): bool
    {
        return $this->samedayLockerRepository->updateLocker($locker, $localId);
    }

    public function deleteById(int $id): void
    {
        $this->samedayLockerRepository->deleteLocker($id);
    }
}
