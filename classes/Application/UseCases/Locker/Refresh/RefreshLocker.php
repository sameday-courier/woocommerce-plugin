<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

use Exception;
use Sameday\Requests\SamedayGetLockersRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\Models\SamedayLocker;
use SamedayCourier\Shipping\Domain\SamedaySettings;

if (!defined('ABSPATH')) {
    exit;
}

final class RefreshLocker
{
    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    public SamedayLockerRepository $samedayLockerRepository;

    /**
     * @var Sameday $sameday
     */
    public Sameday $sameday;

    /**
     * @param RefreshLockerRequest $refreshLockerRequest
     */
    public function __construct(RefreshLockerRequest $refreshLockerRequest)
    {
        $this->samedayLockerRepository = $refreshLockerRequest->samedayLockerRepository;
        $this->sameday = $refreshLockerRequest->sameday;
    }

    /**
     * @return RefreshLockerResponse
     */
    public function execute(): RefreshLockerResponse
    {
        $remoteLockers = [];
        $page = 1;

        do {
            $request = new SamedayGetLockersRequest();
            $request->setPage($page++);

            try {
                $lockers = $this->sameday->getLockers($request);
            } catch (Exception $e) {

                return new RefreshLockerResponse(
                    $e->getMessage(),
                    ResponseNoticeType::ERROR,
                );
            }

            foreach ($lockers->getLockers() as $lockerObject) {
                $locker = $this->samedayLockerRepository->getLockerSameday($lockerObject->getId());
                if (null === $locker) {
                    $this->samedayLockerRepository->addLocker($lockerObject);
                } else {
                    $this->samedayLockerRepository->updateLocker($lockerObject, $locker->getId());
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
        SamedaySettings::setSamedaySyncLockersTs(time());
    }
}
