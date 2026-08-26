<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Refresh\RefreshPickupPoint;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\PickupPointStoreServiceProvider;

final class RefreshPickupPointFactory
{
    /**
     * @return RefreshPickupPoint
     */
    public static function create(): RefreshPickupPoint
    {
        return new RefreshPickupPoint(
            new CourierServiceProvider(),
            new PickupPointStoreServiceProvider(),
        );
    }
}
