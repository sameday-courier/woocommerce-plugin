<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\Service\Refresh\RefreshService;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\ServiceCatalogStoreServiceProvider;

final class RefreshServiceFactory
{
    /**
     * @return RefreshService
     */
    public static function create(): RefreshService
    {
        return new RefreshService(
            new CourierServiceProvider(),
            new ServiceCatalogStoreServiceProvider(),
        );
    }
}
