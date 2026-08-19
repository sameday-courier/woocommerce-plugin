<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetLockersRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierLocker;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\LockerStoreServiceProviderInterface;

final class RefreshLocker
{
    private CourierServiceProviderInterface $courierServiceProvider;

    private LockerStoreServiceProviderInterface $lockerStore;

    private CarrierSettingsProviderInterface $carrierSettingsProvider;

    /**
     * @param RefreshLockerRequest $refreshLockerRequest
     */
    public function __construct(RefreshLockerRequest $refreshLockerRequest)
    {
        $this->courierServiceProvider = $refreshLockerRequest->getCourierServiceProvider();
        $this->lockerStore = $refreshLockerRequest->getLockerStore();
        $this->carrierSettingsProvider = $refreshLockerRequest->getCarrierSettingsProvider();
    }

    /**
     * @return RefreshLockerResponse
     */
    public function execute(): RefreshLockerResponse
    {
        $remoteLockers = [];
        $page = 1;

        do {
            try {
                $lockers = $this->courierServiceProvider->getLockers(new GetLockersRequestDto([], $page++));
            } catch (CourierServiceException $exception) {
                return new RefreshLockerResponse(
                    $exception->getMessage(),
                    ResponseNoticeType::ERROR
                );
            }

            foreach ($lockers->getLockers() as $lockerDto) {
                $locker = $this->lockerStore->getBySamedayId($lockerDto->getId());
                if (null === $locker) {
                    $this->lockerStore->add($lockerDto);
                } elseif (!$this->lockerStore->updateFromRemote($lockerDto, $locker->getId())) {
                    return new RefreshLockerResponse(
                        'Unable to update locker',
                        ResponseNoticeType::ERROR
                    );
                }

                $remoteLockers[] = $lockerDto->getId();
            }
        } while ($page <= $lockers->getPages());

        $localLockers = array_map(
            /**
             * @param CarrierLocker $locker
             *
             * @return array
             */
            static function (CarrierLocker $locker): array {
                return [
                    'id' => $locker->getId(),
                    'locker_id' => (int) $locker->getLockerId(),
                ];
            },
            $this->lockerStore->getAll()
        );

        foreach ($localLockers as $localLocker) {
            if (!in_array($localLocker['locker_id'], $remoteLockers, true)) {
                $this->lockerStore->deleteById((int) $localLocker['id']);
            }
        }

        $this->carrierSettingsProvider->setSamedaySyncLockersTs(time());

        return new RefreshLockerResponse(
            'Lockers successfully refreshed.',
            ResponseNoticeType::SUCCESS
        );
    }
}
