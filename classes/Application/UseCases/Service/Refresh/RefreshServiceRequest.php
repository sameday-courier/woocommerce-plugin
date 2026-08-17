<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Refresh;

use SamedayCourier\Shipping\Domain\Ports\RefreshServiceServiceProviderInterface;

final class RefreshServiceRequest
{
    private RefreshServiceServiceProviderInterface $refreshServiceServiceProvider;

    /**
     * @param RefreshServiceServiceProviderInterface $refreshServiceServiceProvider
     */
    public function __construct(RefreshServiceServiceProviderInterface $refreshServiceServiceProvider)
    {
        $this->refreshServiceServiceProvider = $refreshServiceServiceProvider;
    }

    /**
     * @return RefreshServiceServiceProviderInterface
     */
    public function getRefreshServiceServiceProvider(): RefreshServiceServiceProviderInterface
    {
        return $this->refreshServiceServiceProvider;
    }
}
