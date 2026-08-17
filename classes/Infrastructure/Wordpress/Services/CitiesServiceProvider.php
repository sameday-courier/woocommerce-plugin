<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\DTOs\Requests\CitiesRefreshRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\CitiesRefreshResponseDto;
use SamedayCourier\Shipping\Domain\Ports\CitiesServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CountriesHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Common\Services\FileReadHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\CacheHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\SchemaHandler;

final class CitiesServiceProvider implements CitiesServiceProviderInterface
{
    private DbHandler $dbHandler;

    private SchemaHandler $schemaHandler;

    private SamedayCityRepository $samedayCityRepository;

    private CacheHandler $cacheHandler;

    private CountriesHandlerInterface $countriesHandler;

    public function __construct(
        ?DbHandler $dbHandler = null,
        ?SchemaHandler $schemaHandler = null,
        ?SamedayCityRepository $samedayCityRepository = null,
        ?CacheHandler $cacheHandler = null,
        ?CountriesHandlerInterface $countriesHandler = null
    ) {
        $this->dbHandler = $dbHandler ?? new DbHandler();
        $this->schemaHandler = $schemaHandler ?? new SchemaHandler();
        $this->samedayCityRepository = $samedayCityRepository ?? new SamedayCityRepository($this->dbHandler);
        $this->cacheHandler = $cacheHandler ?? new CacheHandler();
        $this->countriesHandler = $countriesHandler ?? new WooCountriesHandler();
    }

    /**
     * @param CitiesRefreshRequestDto $citiesRefreshRequestDto
     *
     * @return CitiesRefreshResponseDto
     */
    public function refresh(CitiesRefreshRequestDto $citiesRefreshRequestDto): CitiesRefreshResponseDto
    {
        if (false === $this->dbHandler->isTableExists($this->samedayCityRepository->getTableName())) {
            $this->dbHandler->executeQuery($this->schemaHandler->buildCitiesTableQuery());
        }

        $cities = FileReadHandler::readJsonFile('cities');
        if (null === $cities) {
            return new CitiesRefreshResponseDto(
                false,
                'Unable to get cities'
            );
        }

        // Remove all previews unnecessary stored data
        $this->samedayCityRepository->truncate();

        foreach ($cities as $carrierCity) {
            if (array_key_exists($carrierCity->country_code, $this->countriesHandler->getShippingCountries())) {
                $this->samedayCityRepository->addCity($carrierCity);
            }
        }

        $this->cacheHandler->refreshCachedData(
            CarrierConstants::TRANSIENT_CACHE_KEY_FOR_CITIES,
            $this->samedayCityRepository->getCities(),
            2592000
        );

        return new CitiesRefreshResponseDto(
            true,
            'All cities have been refreshed'
        );
    }
}
