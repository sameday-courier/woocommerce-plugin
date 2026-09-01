<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Http\Factories;

use SamedayCourier\Shipping\Application\UseCases\City\Get\GetCities;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CourierServiceProvider;

final class GetCitiesFactory
{
    /**
     * @return GetCities
     */
    public static function create(): GetCities
    {
        return new GetCities(
            new CourierServiceProvider(),
        );
    }
}
