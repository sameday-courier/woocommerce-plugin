<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\Common\Factories;

use SamedayCourier\Shipping\Application\UseCases\City\Refresh\RefreshCityRequest;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CitiesServiceProvider;

final class CityRequestFactory
{
    public function create(): RefreshCityRequest
    {
        return new RefreshCityRequest(
            new CitiesServiceProvider()
        );
    }
}
