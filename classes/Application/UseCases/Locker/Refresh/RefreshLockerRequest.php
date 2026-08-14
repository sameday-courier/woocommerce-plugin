<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CarrierSettingsProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;

final class RefreshLockerRequest
{
    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

    private CourierServiceProviderInterface $courier;

    /**
     * @var CarrierSettingsProviderInterface
     */
    private CarrierSettingsProviderInterface $carrierSettingsProvider;

    /**
     * @param SamedayLockerRepository $samedayLockerRepository
     * @param CourierServiceProviderInterface $courier
     * @param CarrierSettingsProviderInterface $carrierSettingsProvider
     */
    public function __construct(
        SamedayLockerRepository $samedayLockerRepository,
        CourierServiceProviderInterface $courier,
        CarrierSettingsProviderInterface $carrierSettingsProvider
    )
    {
        $this->samedayLockerRepository = $samedayLockerRepository;
        $this->courier = $courier;
        $this->carrierSettingsProvider = $carrierSettingsProvider;
    }

    /**
     * @return SamedayLockerRepository
     */
    public function getSamedayLockerRepository(): SamedayLockerRepository
    {
        return $this->samedayLockerRepository;
    }

    /**
     * @return CourierServiceProviderInterface
     */
    public function getCourier(): CourierServiceProviderInterface
    {
        return $this->courier;
    }

    /**
     * @return CarrierSettingsProviderInterface
     */
    public function getCarrierSettingsProvider(): CarrierSettingsProviderInterface
    {
        return $this->carrierSettingsProvider;
    }
}
