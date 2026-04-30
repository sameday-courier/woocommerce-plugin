<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\DataSync;

use Exception;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Requests\SamedayGetLockersRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Domain\Models\SamedayLocker;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;

if (!defined('ABSPATH')) {
    exit;
}

class RefreshLockers
{
    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

    public function __construct()
    {
        $this->samedayLockerRepository = new SamedayLockerRepository();
    }

    /**
     * @return void
     * @throws SamedaySDKException
     */
    public function syncFromRemote(): void
    {
        $this->updateLockersList(false);
    }

    /**
     * @return void
     *
     * @throws SamedaySDKException
     */
    public function refresh(): void
    {
        if (empty(OptionsHandler::getSamedayOptions())) {
            Redirector::to('admin.php', ['page' => 'sameday_lockers']);
        }

        $this->updateLockersList(true);

        Redirector::to('edit.php', ['post_type' => 'page', 'page' => 'sameday_lockers']);
    }

    /**
     * @param bool $redirectOnApiError
     * @return void
     * @throws SamedaySDKException
     */
    private function updateLockersList(bool $redirectOnApiError): void
    {
        $sameday = new Sameday(SdkInitiator::init());

        $remoteLockers = [];
        $page = 1;
        do {
            $request = new SamedayGetLockersRequest();
            $request->setPage($page++);
            try {
                $lockers = $sameday->getLockers($request);
            } catch (Exception $e) {
                if ($redirectOnApiError) {
                    Redirector::to('admin.php', ['page' => 'sameday_lockers']);
                }

                return;
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
                return array(
                    'id' => $locker->getId(),
                    'locker_id' => (int) $locker->getLockerId()
                );
            },
            $this->samedayLockerRepository->getLockers()
        );

        foreach ($localLockers as $localLocker) {
            if (!in_array($localLocker['locker_id'], $remoteLockers, true)) {
                $this->samedayLockerRepository->deleteLocker((int) $localLocker['id']);
            }
        }

        $this->updateLastSyncTimestamp();
    }

    /**
     * @return void
     */
    private function updateLastSyncTimestamp(): void
    {
        $samedayOptions = OptionsHandler::getSamedayOptions();
        $samedayOptions['sameday_sync_lockers_ts'] = time();
        OptionsHandler::setSamedayOptions($samedayOptions);
    }
}
