<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

use Sameday\Sameday;
use SamedayCourier\Shipping\Domain\Ports\SamedaySettingsProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayLockerRepository;

final class RefreshLockerRequest
{
    /**
     * @var SamedayLockerRepository $samedayLockerRepository
     */
    private SamedayLockerRepository $samedayLockerRepository;

    /**
     * @var Sameday $sameday
     */
    private Sameday $sameday;

    /**
     * @var SamedaySettingsProviderInterface
     */
    private SamedaySettingsProviderInterface $samedaySettingsProvider;

    /**
     * @param SamedayLockerRepository $samedayLockerRepository
     * @param Sameday $sameday
     * @param SamedaySettingsProviderInterface $samedaySettingsProvider
     */
    public function __construct(
        SamedayLockerRepository $samedayLockerRepository,
        Sameday $sameday,
        SamedaySettingsProviderInterface $samedaySettingsProvider
    )
    {
        $this->samedayLockerRepository = $samedayLockerRepository;
        $this->sameday = $sameday;
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
     * @return Sameday
     */
    public function getSameday(): Sameday
    {
        return $this->sameday;
    }

    /**
     * @return SamedaySettingsProviderInterface
     */
    public function getSamedaySettingsProvider(): SamedaySettingsProviderInterface
    {
        return $this->samedaySettingsProvider;
    }
}
