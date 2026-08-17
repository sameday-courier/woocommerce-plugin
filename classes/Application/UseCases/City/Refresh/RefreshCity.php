<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Refresh;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\Ports\CityCatalogStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CitySourceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CountriesHandlerInterface;

final class RefreshCity
{
    private CityCatalogStoreServiceProviderInterface $cityCatalogStore;

    private CitySourceProviderInterface $citySourceProvider;

    private CountriesHandlerInterface $countriesHandler;

    public function __construct(RefreshCityRequest $refreshCitiesRequest)
    {
        $this->cityCatalogStore = $refreshCitiesRequest->getCityCatalogStore();
        $this->citySourceProvider = $refreshCitiesRequest->getCitySourceProvider();
        $this->countriesHandler = $refreshCitiesRequest->getCountriesHandler();
    }

    public function execute(): RefreshCityResponse
    {
        $this->cityCatalogStore->ensureTableExists();

        $cities = $this->citySourceProvider->readCities();
        if (null === $cities) {
            return new RefreshCityResponse(
                'Unable to get cities',
                ResponseNoticeType::ERROR
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
            ResponseNoticeType::SUCCESS
        );
    }
}
