<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\Checkout;

use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLockerRequest;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Controllers\AbstractNoPrivController;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories\RefreshLockerFactory;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\LockerChoicesProvider;

/**
 * Refreshes the locker nomenclator on demand from checkout (classic + Blocks) and returns the
 * grouped dropdown choices as JSON.
 *
 * Replaces the previous side effect that ran RefreshLocker inside the shipping method's
 * calculate_shipping(). The dropdown-only condition, service eligibility, and TTL are pre-checked
 * client side, while this controller re-checks the TTL and mode so the courier API cannot be spammed.
 */
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

        // The locally synced list only backs the dropdown; the map picks lockers via the SDK.
        if ($settings->isLockersMapEnabled()) {
            $this->sendJsonErrorResponse(['message' => 'Locker map mode is enabled.']);

            return;
        }

        // Authoritative TTL gate: clients optimize the request away, the server enforces it.
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
