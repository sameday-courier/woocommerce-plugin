<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh;

use SamedayCourier\Shipping\Domain\Ports\RefreshPickupPointServiceProviderInterface;

final class RefreshPickupPointRequest
{
    private RefreshPickupPointServiceProviderInterface $refreshPickupPointServiceProvider;

    /**
     * @param RefreshPickupPointServiceProviderInterface $refreshPickupPointServiceProvider
     */
    public function __construct(RefreshPickupPointServiceProviderInterface $refreshPickupPointServiceProvider)
    {
        $this->refreshPickupPointServiceProvider = $refreshPickupPointServiceProvider;
    }

    /**
     * @return RefreshPickupPointServiceProviderInterface
     */
    public function getRefreshPickupPointServiceProvider(): RefreshPickupPointServiceProviderInterface
    {
        return $this->refreshPickupPointServiceProvider;
    }
}
