<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\PickupPoint\Delete\DeletePickupPoint;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;

final class DeletePickupPointFactory
{
    /**
     * @return DeletePickupPoint
     */
    public static function create(): DeletePickupPoint
    {
        return new DeletePickupPoint(
            new CourierServiceProvider(),
        );
    }
}
