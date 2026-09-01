<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Domain\DTOs\CitySourceDto;
use SamedayCourier\Shipping\Domain\Ports\CityCatalogStoreServiceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\CacheHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\SchemaHandler;

final class CityCatalogStoreServiceProvider implements CityCatalogStoreServiceProviderInterface
{
    private DbHandler $dbHandler;

    private SchemaHandler $schemaHandler;

    private SamedayCityRepository $samedayCityRepository;

    private CacheHandler $cacheHandler;

    /**
     * @param ?DbHandler $dbHandler
     * @param ?SchemaHandler $schemaHandler
     * @param ?SamedayCityRepository $samedayCityRepository
     * @param ?CacheHandler $cacheHandler
     */
    public function __construct(
        ?DbHandler $dbHandler = null,
        ?SchemaHandler $schemaHandler = null,
        ?SamedayCityRepository $samedayCityRepository = null,
        ?CacheHandler $cacheHandler = null
    ) {
        $this->dbHandler = $dbHandler ?? new DbHandler();
        $this->schemaHandler = $schemaHandler ?? new SchemaHandler();
        $this->samedayCityRepository = $samedayCityRepository ?? new SamedayCityRepository($this->dbHandler);
        $this->cacheHandler = $cacheHandler ?? new CacheHandler();
    }

    /**
     * @return void
     */
    public function ensureTableExists(): void
    {
        if (false === $this->dbHandler->isTableExists($this->samedayCityRepository->getTableName())) {
            $this->dbHandler->executeQuery($this->schemaHandler->buildCitiesTableQuery());
        }
    }

    /**
     * @return void
     */
    public function truncate(): void
    {
        $this->samedayCityRepository->truncate();
    }

    /**
     * @param CitySourceDto $city
     *
     * @return void
     */
    public function insert(CitySourceDto $city): void
    {
        $this->samedayCityRepository->addCity($city);
    }

    /**
     * @return void
     */
    public function refreshCache(): void
    {
        $this->cacheHandler->refreshCachedData(
            CarrierConstants::TRANSIENT_CACHE_KEY_FOR_CITIES,
            $this->samedayCityRepository->getCities(),
            2592000
        );
    }
}
