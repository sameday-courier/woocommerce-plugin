<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Service\Refresh;

use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\ServiceCatalogStoreServiceProviderInterface;

final class RefreshServiceRequest
{
    private CourierServiceProviderInterface $courierServiceProvider;

    private ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore;

    public function __construct(
        CourierServiceProviderInterface $courierServiceProvider,
        ServiceCatalogStoreServiceProviderInterface $serviceCatalogStore
    ) {
        $this->courierServiceProvider = $courierServiceProvider;
        $this->serviceCatalogStore = $serviceCatalogStore;
    }

    public function getCourierServiceProvider(): CourierServiceProviderInterface
    {
        return $this->courierServiceProvider;
    }

    public function getServiceCatalogStore(): ServiceCatalogStoreServiceProviderInterface
    {
        return $this->serviceCatalogStore;
    }
}
