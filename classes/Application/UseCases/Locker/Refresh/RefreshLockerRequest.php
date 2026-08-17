<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Locker\Refresh;

use SamedayCourier\Shipping\Domain\Ports\RefreshLockerServiceProviderInterface;

final class RefreshLockerRequest
{
    private RefreshLockerServiceProviderInterface $refreshLockerServiceProvider;

    /**
     * @param RefreshLockerServiceProviderInterface $refreshLockerServiceProvider
     */
    public function __construct(RefreshLockerServiceProviderInterface $refreshLockerServiceProvider)
    {
        $this->refreshLockerServiceProvider = $refreshLockerServiceProvider;
    }

    /**
     * @return RefreshLockerServiceProviderInterface
     */
    public function getRefreshLockerServiceProvider(): RefreshLockerServiceProviderInterface
    {
        return $this->refreshLockerServiceProvider;
    }
}
