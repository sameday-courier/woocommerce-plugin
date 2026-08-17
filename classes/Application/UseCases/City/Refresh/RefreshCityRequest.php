<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Refresh;

use SamedayCourier\Shipping\Domain\Ports\CityCatalogStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CitySourceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CountriesHandlerInterface;

final class RefreshCityRequest
{
    private CityCatalogStoreServiceProviderInterface $cityCatalogStore;

    private CitySourceProviderInterface $citySourceProvider;

    private CountriesHandlerInterface $countriesHandler;

    public function __construct(
        CityCatalogStoreServiceProviderInterface $cityCatalogStore,
        CitySourceProviderInterface $citySourceProvider,
        CountriesHandlerInterface $countriesHandler
    ) {
        $this->cityCatalogStore = $cityCatalogStore;
        $this->citySourceProvider = $citySourceProvider;
        $this->countriesHandler = $countriesHandler;
    }

    public function getCityCatalogStore(): CityCatalogStoreServiceProviderInterface
    {
        return $this->cityCatalogStore;
    }

    public function getCitySourceProvider(): CitySourceProviderInterface
    {
        return $this->citySourceProvider;
    }

    public function getCountriesHandler(): CountriesHandlerInterface
    {
        return $this->countriesHandler;
    }
}
