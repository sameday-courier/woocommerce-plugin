<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\Locker\Refresh\RefreshLocker;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CarrierSettingsServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\LockerStoreServiceProvider;

final class RefreshLockerFactory
{
    /**
     * @return RefreshLocker
     */
    public static function create(): RefreshLocker
    {
        return new RefreshLocker(
            new CourierServiceProvider(),
            new LockerStoreServiceProvider(),
            new CarrierSettingsServiceProvider(),
        );
    }
}
