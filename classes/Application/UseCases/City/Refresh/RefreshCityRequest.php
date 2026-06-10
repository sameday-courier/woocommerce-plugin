<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Refresh;

use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Application\Sql\SchemaHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CacheHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use stdClass;

if (!defined('ABSPATH')) {
    exit;
}

final class RefreshCityRequest
{
    /**
     * @var SamedayCityRepository $samedayCityRepository
     */
    public SamedayCityRepository $samedayCityRepository;

    /**
     * @var CacheHandler $cacheHandler
     */
    public CacheHandler $cacheHandler;

    /**
     * @var SchemaHandler $schemaHandler
     */
    public SchemaHandler $schemaHandler;

    /**
     * @var DbHandler $dbHandler
     */
    public DbHandler $dbHandler;

    /**
     * @param CacheHandler $cacheHandler
     * @param DbHandler $dbHandler
     * @param SchemaHandler $schemaHandler
     * @param SamedayCityRepository $samedayCityRepository
     */
    public function __construct(
        SchemaHandler $schemaHandler,
        DbHandler $dbHandler,
        SamedayCityRepository $samedayCityRepository,
        CacheHandler $cacheHandler
    )
    {
        $this->samedayCityRepository = $samedayCityRepository;
        $this->cacheHandler = $cacheHandler;
        $this->schemaHandler = $schemaHandler;
        $this->dbHandler = $dbHandler;
    }
}
