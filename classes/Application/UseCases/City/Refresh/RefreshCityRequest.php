<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Refresh;

use SamedayCourier\Shipping\Domain\Ports\CitiesServiceProviderInterface;

final class RefreshCityRequest
{
    /**
     * @var CitiesServiceProviderInterface $citiesServiceProvider
     */
    private CitiesServiceProviderInterface $citiesServiceProvider;

    /**
     * @param CitiesServiceProviderInterface $citiesServiceProvider
     */
    public function __construct(CitiesServiceProviderInterface $citiesServiceProvider)
    {
        $this->citiesServiceProvider = $citiesServiceProvider;
    }

    /**
     * @return CitiesServiceProviderInterface
     */
    public function getCitiesServiceProvider(): CitiesServiceProviderInterface
    {
        return $this->citiesServiceProvider;
    }
}
