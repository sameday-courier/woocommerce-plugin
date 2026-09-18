<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\Awb\Generate\GenerateAwb;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooOrderStatusUpdater;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooSamedayShippingHdAddressParser;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooStateCodeResolver;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderAwbStoreServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\OrderShippingChangesServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PickupPointStoreServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\ServiceCatalogStoreServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayCityRepository;

final class GenerateAwbFactory
{
    /**
     * @return GenerateAwb
     */
    public static function create(): GenerateAwb
    {
        return new GenerateAwb(
            new ServiceCatalogStoreServiceProvider(),
            new PickupPointStoreServiceProvider(),
            new OrderAwbStoreServiceProvider(),
            new CourierServiceProvider(),
            new OrderShippingChangesServiceProvider(),
            new WooSamedayShippingHdAddressParser(),
            new WooStateCodeResolver(new WooCountriesHandler()),
            new SamedayCityRepository(),
            new CarrierSettingsServiceProvider(),
            new WooOrderStatusUpdater()
        );
    }
}
