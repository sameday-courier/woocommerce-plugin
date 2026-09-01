<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\Service\Edit\EditService;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\ServiceCatalogStoreServiceProvider;

final class EditServiceFactory
{
    /**
     * @return EditService
     */
    public static function create(): EditService
    {
        return new EditService(
            new ServiceCatalogStoreServiceProvider(),
        );
    }
}
