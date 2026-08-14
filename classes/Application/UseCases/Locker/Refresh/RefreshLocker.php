<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\GetLockersRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Models\SamedayLocker;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\SamedaySettingsProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;

final class RefreshLocker
{
    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    public SamedayLockerRepository $samedayLockerRepository;

    public CourierServiceProviderInterface $courier;

    /**
     * @var SamedaySettingsProviderInterface
     */
    private SamedaySettingsProviderInterface $samedaySettingsProvider;

    /**
     * @param RefreshLockerRequest $refreshLockerRequest
     */
    public function __construct(RefreshLockerRequest $refreshLockerRequest)
    {
        $this->samedayLockerRepository = $refreshLockerRequest->getSamedayLockerRepository();
        $this->courier = $refreshLockerRequest->getCourier();
        $this->samedaySettingsProvider = $refreshLockerRequest->getSamedaySettingsProvider();
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
                $lockers = $this->courier->getLockers(new GetLockersRequestDto([], $page++));
            } catch (CourierServiceException $e) {

                return new RefreshLockerResponse(
                    $e->getMessage(),
                    ResponseNoticeType::ERROR,
                );
            }

            foreach ($lockers->getLockers() as $lockerObject) {
                $locker = $this->samedayLockerRepository->getLockerSameday($lockerObject->getId());
                if (null === $locker) {
                    $this->samedayLockerRepository->addLocker($lockerObject);
                } elseif (!$this->samedayLockerRepository->updateLocker($lockerObject, $locker->getId())) {
                    return new RefreshLockerResponse(
                        'Unable to update locker',
                        ResponseNoticeType::ERROR,
                    );
                }

                $remoteLockers[] = $lockerObject->getId();
            }
        } while ($page <= $lockers->getPages());

        $localLockers = array_map(
            static function (SamedayLocker $locker) {
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

        return new RefreshLockerResponse(
            "Lockers successfully refreshed.",
            ResponseNoticeType::SUCCESS,
        );
    }

    /**
     * @return void
     */
    private function updateLastSyncTimestamp(): void
    {
        $this->samedaySettingsProvider->setSamedaySyncLockersTs(time());
    }
}
