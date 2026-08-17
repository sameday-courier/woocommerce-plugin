<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\Requests\GetLockersRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RefreshLockerRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\RefreshLockerResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierLocker;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\RefreshLockerServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;

final class RefreshLockerServiceProvider implements RefreshLockerServiceProviderInterface
{
    private SamedayLockerRepository $samedayLockerRepository;

    private CourierServiceProviderInterface $courier;

    private CarrierSettingsProviderInterface $carrierSettingsProvider;

    public function __construct(
        ?SamedayLockerRepository $samedayLockerRepository = null,
        ?CourierServiceProviderInterface $courier = null,
        ?CarrierSettingsProviderInterface $carrierSettingsProvider = null
    ) {
        $this->samedayLockerRepository = $samedayLockerRepository ?? new SamedayLockerRepository();
        $this->courier = $courier ?? new CourierServiceProvider();
        $this->carrierSettingsProvider = $carrierSettingsProvider ?? new CarrierSettingsServiceProvider();
    }

    /**
     * @param RefreshLockerRequestDto $refreshLockerRequestDto
     *
     * @return RefreshLockerResponseDto
     */
    public function refresh(RefreshLockerRequestDto $refreshLockerRequestDto): RefreshLockerResponseDto
    {
        $remoteLockers = [];
        $page = 1;

        do {
            try {
                $lockers = $this->courier->getLockers(new GetLockersRequestDto([], $page++));
            } catch (CourierServiceException $e) {
                return new RefreshLockerResponseDto(
                    false,
                    $e->getMessage()
                );
            }

            foreach ($lockers->getLockers() as $lockerObject) {
                $locker = $this->samedayLockerRepository->getLockerSameday($lockerObject->getId());
                if (null === $locker) {
                    $this->samedayLockerRepository->addLocker($lockerObject);
                } elseif (!$this->samedayLockerRepository->updateLocker($lockerObject, $locker->getId())) {
                    return new RefreshLockerResponseDto(
                        false,
                        'Unable to update locker'
                    );
                }

                $remoteLockers[] = $lockerObject->getId();
            }
        } while ($page <= $lockers->getPages());

        $localLockers = array_map(
            static function (CarrierLocker $locker) {
                return [
                    'id' => $locker->getId(),
                    'locker_id' => (int) $locker->getLockerId(),
                ];
            },
            $this->samedayLockerRepository->getLockers()
        );

        foreach ($localLockers as $localLocker) {
            if (!in_array($localLocker['locker_id'], $remoteLockers, true)) {
                $this->samedayLockerRepository->deleteLocker((int) $localLocker['id']);
            }
        }

        $this->updateLastSyncTimestamp();

        return new RefreshLockerResponseDto(
            true,
            "Lockers successfully refreshed."
        );
    }

    /**
     * @return void
     */
    private function updateLastSyncTimestamp(): void
    {
        $this->carrierSettingsProvider->setSamedaySyncLockersTs(time());
    }
}
