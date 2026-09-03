<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Refresh;

use SamedayCourier\Shipping\Application\Common\AbstractUseCase;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;
use SamedayCourier\Shipping\Domain\Ports\CityCatalogStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CitySourceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CountriesHandlerInterface;

/**
 * @extends AbstractUseCase<RefreshCityRequest, RefreshCityResponse>
 *
 * @method RefreshCityResponse execute(RefreshCityRequest $request)
 */
final class RefreshCity extends AbstractUseCase
{
    /**
     * @var CityCatalogStoreServiceProviderInterface $cityCatalogStore
     */
    private CityCatalogStoreServiceProviderInterface $cityCatalogStore;

    /**
     * @var CitySourceProviderInterface $citySourceProvider
     */
    private CitySourceProviderInterface $citySourceProvider;

    /**
     * @var CountriesHandlerInterface $countriesHandler
     */
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
     * @param RefreshCityRequest $request
     * @return RefreshCityResponse
     */
    protected function processAction(RequestInterface $request): RefreshCityResponse
    {
        $this->cityCatalogStore->ensureTableExists();

        $cities = $this->citySourceProvider->readCities();
        if (null === $cities) {
            return new RefreshCityResponse(
                'Unable to get cities',
                true
            );
        }

        $this->cityCatalogStore->truncate();

        $shippingCountries = $this->countriesHandler->getShippingCountries();
        foreach ($cities as $city) {
            if (array_key_exists($city->getCountryCode(), $shippingCountries)) {
                $this->cityCatalogStore->insert($city);
            }
        }

        $this->cityCatalogStore->refreshCache();

        return new RefreshCityResponse(
            'All cities have been refreshed',
            false
        );
    }
}
