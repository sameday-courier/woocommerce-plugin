<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\LockerStoreServiceProviderInterface;

final class RefreshLockerRequest
{
    private CourierServiceProviderInterface $courierServiceProvider;

    private LockerStoreServiceProviderInterface $lockerStore;

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
     * @return CourierServiceProviderInterface
     */
    public function getCourierServiceProvider(): CourierServiceProviderInterface
    {
        return $this->courierServiceProvider;
    }

    /**
     * @return LockerStoreServiceProviderInterface
     */
    public function getLockerStore(): LockerStoreServiceProviderInterface
    {
        return $this->lockerStore;
    }

    /**
     * @return CarrierSettingsProviderInterface
     */
    public function getCarrierSettingsProvider(): CarrierSettingsProviderInterface
    {
        return $this->carrierSettingsProvider;
    }
}
