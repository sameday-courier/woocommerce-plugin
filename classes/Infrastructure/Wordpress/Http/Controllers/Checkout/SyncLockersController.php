<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Checkout;

use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLockerRequest;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractNoPrivController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\RefreshLockerFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\LockerChoicesProvider;

final class SyncLockersController extends AbstractNoPrivController
{
    private const ACTION = 'refresh_lockers_checkout';

    /**
     * @var CarrierSettingsServiceProvider $carrierSettingsProvider
     */
    private CarrierSettingsServiceProvider $carrierSettingsProvider;

    /**
     * @var LockerChoicesProvider $lockerChoicesProvider
     */
    private LockerChoicesProvider $lockerChoicesProvider;

    /**
     * @param CarrierSettingsServiceProvider|null $carrierSettingsProvider
     * @param LockerChoicesProvider|null $lockerChoicesProvider
     */
    public function __construct(
        ?CarrierSettingsServiceProvider $carrierSettingsProvider = null,
        ?LockerChoicesProvider $lockerChoicesProvider = null
    ) {
        $this->carrierSettingsProvider = $carrierSettingsProvider ?? new CarrierSettingsServiceProvider();
        $this->lockerChoicesProvider = $lockerChoicesProvider ?? new LockerChoicesProvider();
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return self::ACTION;
    }

    /**
     * @param array $inputParams
     *
     * @return void
     */
    protected function processNoPrivAction(array $inputParams): void
    {
        $settings = $this->carrierSettingsProvider->get();

        if ($settings->isLockersMapEnabled()) {
            $this->sendJsonErrorResponse(['message' => 'Locker map mode is enabled.']);

            return;
        }

        if (time() > ($settings->getSamedaySyncLockersTs() + CarrierConstants::LOCKERS_SYNC_TTL)) {
            $result = RefreshLockerFactory::create()->execute(new RefreshLockerRequest());

            if ($result->hasError()) {
                $this->sendJsonErrorResponse(['message' => $result->getNoticeMessage()]);

                return;
            }
        }

        $selectedLockerId = (int) ($inputParams['selected_locker_id'] ?? 0);

        $this->sendJsonSuccessResponse([
            'lockersByCity' => $this->lockerChoicesProvider->groupedByCity($selectedLockerId ?: null),
        ]);
    }
}
