<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Refresh;

use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\SchemaHandler;
use SamedayCourier\Shipping\Domain\Ports\CountriesHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CacheHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;

if (!defined('ABSPATH')) {
    exit;
}

final class RefreshCityRequest
{
    /**
     * @var SamedayCityRepository $samedayCityRepository
     */
    private SamedayCityRepository $samedayCityRepository;

    /**
     * @var CacheHandler $cacheHandler
     */
    private CacheHandler $cacheHandler;

    /**
     * @var SchemaHandler $schemaHandler
     */
    private SchemaHandler $schemaHandler;

    /**
     * @var DbHandler $dbHandler
     */
    private DbHandler $dbHandler;

    /**
     * @var CountriesHandlerInterface $countriesHandler
     */
    private CountriesHandlerInterface $countriesHandler;

    /**
     * @param SchemaHandler $schemaHandler
     * @param DbHandler $dbHandler
     * @param SamedayCityRepository $samedayCityRepository
     * @param CacheHandler $cacheHandler
     * @param CountriesHandlerInterface $countriesHandler
     */
    public function __construct(
        SchemaHandler $schemaHandler,
        DbHandler $dbHandler,
        SamedayCityRepository $samedayCityRepository,
        CacheHandler $cacheHandler,
        CountriesHandlerInterface $countriesHandler
    )
    {
        $this->samedayCityRepository = $samedayCityRepository;
        $this->cacheHandler = $cacheHandler;
        $this->schemaHandler = $schemaHandler;
        $this->dbHandler = $dbHandler;
        $this->countriesHandler = $countriesHandler;
    }

    /**
     * @return SamedayCityRepository
     */
    public function getSamedayCityRepository(): SamedayCityRepository
    {
        return $this->samedayCityRepository;
    }

    /**
     * @return CacheHandler
     */
    public function getCacheHandler(): CacheHandler
    {
        return $this->cacheHandler;
    }

    /**
     * @return SchemaHandler
     */
    public function getSchemaHandler(): SchemaHandler
    {
        return $this->schemaHandler;
    }

    /**
     * @return DbHandler
     */
    public function getDbHandler(): DbHandler
    {
        return $this->dbHandler;
    }

    /**
     * @return CountriesHandlerInterface
     */
    public function getCountriesHandler(): CountriesHandlerInterface
    {
        return $this->countriesHandler;
    }
}
