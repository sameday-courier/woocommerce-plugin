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

    /**
     * @param CityCatalogStoreServiceProviderInterface $cityCatalogStore
     * @param CitySourceProviderInterface $citySourceProvider
     * @param CountriesHandlerInterface $countriesHandler
     */
    public function __construct(
        CityCatalogStoreServiceProviderInterface $cityCatalogStore,
        CitySourceProviderInterface $citySourceProvider,
        CountriesHandlerInterface $countriesHandler
    ) {
        $this->cityCatalogStore = $cityCatalogStore;
        $this->citySourceProvider = $citySourceProvider;
        $this->countriesHandler = $countriesHandler;
    }

    /**
     * @return CityCatalogStoreServiceProviderInterface
     */
    public function getCityCatalogStore(): CityCatalogStoreServiceProviderInterface
    {
        return $this->cityCatalogStore;
    }

    /**
     * @return CitySourceProviderInterface
     */
    public function getCitySourceProvider(): CitySourceProviderInterface
    {
        return $this->citySourceProvider;
    }

    /**
     * @return CountriesHandlerInterface
     */
    public function getCountriesHandler(): CountriesHandlerInterface
    {
        return $this->countriesHandler;
    }
}
