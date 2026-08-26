<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\City\Refresh\RefreshCity;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CityCatalogStoreServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CitySourceServiceProvider;

final class RefreshCityFactory
{
    /**
     * @return RefreshCity
     */
    public static function create(): RefreshCity
    {
        return new RefreshCity(
            new CityCatalogStoreServiceProvider(),
            new CitySourceServiceProvider(),
            new WooCountriesHandler(),
        );
    }
}
