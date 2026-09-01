<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;

final class LockerChoicesProvider
{
    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

    /**
     * @param SamedayLockerRepository|null $samedayLockerRepository
     */
    public function __construct(?SamedayLockerRepository $samedayLockerRepository = null)
    {
        $this->samedayLockerRepository = $samedayLockerRepository ?? new SamedayLockerRepository();
    }

    /**
     * @param int|null $selectedLockerId
     *
     * @return array<string, array<int, array{id: int|string, label: string, selected: bool}>>
     */
    public function groupedByCity(?int $selectedLockerId = null): array
    {
        $lockersByCity = [];

        foreach ($this->samedayLockerRepository->getCitiesWithLockers() as $city) {
            $cityName = $city->getCity();
            if (null === $cityName) {
                continue;
            }

            $cityLabel = $cityName . ' (' . $city->getCounty() . ')';
            $cityLockers = [];

            foreach ($this->samedayLockerRepository->getLockersByCity($cityName) as $locker) {
                $cityLockers[] = [
                    'id' => $locker->getLockerId(),
                    'label' => $locker->getName() . ' - ' . $locker->getAddress(),
                    'selected' => null !== $selectedLockerId && $selectedLockerId === $locker->getLockerId(),
                ];
            }

            $lockersByCity[$cityLabel] = $cityLockers;
        }

        return $lockersByCity;
    }
}
