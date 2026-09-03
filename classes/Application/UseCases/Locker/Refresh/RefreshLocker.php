<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

use SamedayCourier\Shipping\Application\Common\AbstractUseCase;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetLockersRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\CarrierLocker;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\LockerStoreServiceProviderInterface;

/**
 * @extends AbstractUseCase<RefreshLockerRequest, RefreshLockerResponse>
 *
 * @method RefreshLockerResponse execute(RefreshLockerRequest $request)
 */
final class RefreshLocker extends AbstractUseCase
{
    /**
     * @var CourierServiceProviderInterface $courierServiceProvider
     */
    private CourierServiceProviderInterface $courierServiceProvider;

    /**
     * @var LockerStoreServiceProviderInterface $lockerStore
     */
    private LockerStoreServiceProviderInterface $lockerStore;

    /**
     * @var CarrierSettingsProviderInterface $carrierSettingsProvider
     */
    private CarrierSettingsProviderInterface $carrierSettingsProvider;

    /**
     * @param CourierServiceProviderInterface $courierServiceProvider
     * @param LockerStoreServiceProviderInterface $lockerStore
     * @param CarrierSettingsProviderInterface $carrierSettingsProvider
     */
    public function __construct(
        CourierServiceProviderInterface $courierServiceProvider,
        LockerStoreServiceProviderInterface $lockerStore,
        CarrierSettingsProviderInterface $carrierSettingsProvider
    ) {
        $this->courierServiceProvider = $courierServiceProvider;
        $this->lockerStore = $lockerStore;
        $this->carrierSettingsProvider = $carrierSettingsProvider;
    }

    /**
     * @param RefreshLockerRequest $request
     * @return RefreshLockerResponse
     */
    protected function processAction(RequestInterface $request): RefreshLockerResponse
    {
        $remoteLockers = [];
        $page = 1;

        do {
            try {
                $lockers = $this->courierServiceProvider->getLockers(new GetLockersRequestDto([], $page++));
            } catch (CourierServiceException $exception) {
                return new RefreshLockerResponse(
                    $exception->getMessage(),
                    true
                );
            }

            foreach ($lockers->getLockers() as $lockerDto) {
                $locker = $this->lockerStore->getBySamedayId($lockerDto->getId());
                if (null === $locker) {
                    $this->lockerStore->add($lockerDto);
                } elseif (!$this->lockerStore->updateFromRemote($lockerDto, $locker->getId())) {
                    return new RefreshLockerResponse(
                        'Unable to update locker',
                        true
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
            false
        );
    }
}
