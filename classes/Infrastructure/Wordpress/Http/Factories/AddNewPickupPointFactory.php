<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew\AddNewPickupPoint;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;

final class AddNewPickupPointFactory
{
    /**
     * @return AddNewPickupPoint
     */
    public static function create(): AddNewPickupPoint
    {
        return new AddNewPickupPoint(
            new CourierServiceProvider(),
        );
    }
}
