<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\SamedaySettingsProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;

final class RefreshLockerRequest
{
    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

    private CourierServiceProviderInterface $courier;

    /**
     * @var SamedaySettingsProviderInterface
     */
    private SamedaySettingsProviderInterface $samedaySettingsProvider;

    /**
     * @param SamedayLockerRepository $samedayLockerRepository
     * @param CourierServiceProviderInterface $courier
     * @param SamedaySettingsProviderInterface $samedaySettingsProvider
     */
    public function __construct(
        SamedayLockerRepository $samedayLockerRepository,
        CourierServiceProviderInterface $courier,
        SamedaySettingsProviderInterface $samedaySettingsProvider
    )
    {
        $this->samedayLockerRepository = $samedayLockerRepository;
        $this->courier = $courier;
        $this->samedaySettingsProvider = $samedaySettingsProvider;
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
     * @return SamedaySettingsProviderInterface
     */
    public function getSamedaySettingsProvider(): SamedaySettingsProviderInterface
    {
        return $this->samedaySettingsProvider;
    }
}
