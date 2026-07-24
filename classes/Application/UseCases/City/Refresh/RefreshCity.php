<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Refresh;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Application\Sql\SchemaHandler;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\FileReadHandler;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\WooCountriesHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CacheHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class RefreshCity
{
    /**
     * @var CacheHandler $cacheHandler
     */
    private CacheHandler $cacheHandler;

    /**
     * @var DbHandler $dbHandler
     */
    private DbHandler $dbHandler;

    /**
     * @var SchemaHandler $schemaHandler
     */
    private SchemaHandler $schemaHandler;

    /**
     * @var SamedayCityRepository $samedayCityRepository
     */
    private SamedayCityRepository $samedayCityRepository;

    /**
     * @param RefreshCityRequest $refreshCitiesRequest
     */
    public function __construct(RefreshCityRequest $refreshCitiesRequest)
    {
        $this->cacheHandler = $refreshCitiesRequest->cacheHandler;
        $this->dbHandler = $refreshCitiesRequest->dbHandler;
        $this->schemaHandler = $refreshCitiesRequest->schemaHandler;
        $this->samedayCityRepository = $refreshCitiesRequest->samedayCityRepository;
    }

    /**
     * @return RefreshCityResponse
     */
    public function execute(): RefreshCityResponse
    {
        if (false === $this->dbHandler->isTableExists($this->samedayCityRepository->getTableName())) {
            $this->dbHandler->executeQuery($this->schemaHandler->buildCitiesTableQuery());
        }

        $cities = FileReadHandler::readJsonFile("cities");
        if (null === $cities) {
            return new RefreshCityResponse(
                'Unable to get cities',
                ResponseNoticeType::ERROR,
            );
        }

        // Remove all previews unnecessary stored data
        $this->samedayCityRepository->truncate();

        foreach ($cities as $samedayCity) {
            if (array_key_exists($samedayCity->country_code, WooCountriesHandler::getShippingCountries())) {
                $this->samedayCityRepository->addCity($samedayCity);
            }
        }

        $this->cacheHandler->refreshCachedData(
            SamedayConstants::TRANSIENT_CACHE_KEY_FOR_CITIES,
            $this->samedayCityRepository->getCities(),
            2592000
        );

        return new RefreshCityResponse(
            "All cities have been refreshed",
            ResponseNoticeType::SUCCESS,
        );
    }
}
