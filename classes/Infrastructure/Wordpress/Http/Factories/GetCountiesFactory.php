<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\County\Get\GetCounties;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;

final class GetCountiesFactory
{
    /**
     * @return GetCounties
     */
    public static function create(): GetCounties
    {
        return new GetCounties(
            new CourierServiceProvider(),
        );
    }
}
