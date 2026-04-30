<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\DataSync;

use Exception;
use JsonException;
use Sameday\Exceptions\SamedaySDKException;
use Sameday\Requests\SamedayGetLockersRequest;
use Sameday\Sameday;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayCityRepository;
use SamedayCourier\Shipping\Application\Sql\Repository\Sameday\SamedayLockerRepository;
use SamedayCourier\Shipping\Application\Sql\SchemaHandler;
use SamedayCourier\Shipping\Domain\Models\SamedayLocker;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\SamedayApi\SdkInitiator;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\Admin\Redirector;
use SamedayCourier\Shipping\Infrastructure\Woo\Services\OptionsHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CacheHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CacheHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\CachingHandlerInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandlerInterface;
use SamedayCourier\Shipping\Utils\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class RefreshCities
{
    /**
     * @var SamedayCityRepository $samedayCityRepository
     */
    private SamedayCityRepository $samedayCityRepository;

    /**
     * @var DbHandlerInterface $dbHandler
     */
    private DbHandlerInterface $dbHandler;

    /**
     * @var SchemaHandler $schemaHandler
     */
    private SchemaHandler $schemaHandler;

    /**
     * @var CacheHandlerInterface
     */
    private CacheHandlerInterface $cacheHandler;

    public function __construct()
    {
        $this->dbHandler = new DbHandler();
        $this->schemaHandler = new SchemaHandler();
        $this->samedayCityRepository = new SamedayCityRepository($this->dbHandler);
        $this->cacheHandler = new CacheHandler();
    }

    /**
     * @return void
     */
    public function refresh(): void
    {
        if (false === $this->dbHandler->isTableExists($this->samedayCityRepository->getTableName())) {
            $this->dbHandler->executeQuery($this->schemaHandler->buildCitiesTableQuery());
        }

        if (!file_exists($file = plugin_dir_path(__FILE__) . 'cities.json')) {
            return;
        }

        try {
            $cities = json_decode(file_get_contents($file), false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return;
        }

        // Remove all previews unnecessary stored data
        $this->samedayCityRepository->truncate();

        foreach ($cities as $samedayCity) {
            if (array_key_exists($samedayCity->country_code, WC()->countries->get_shipping_countries())) {
                $this->samedayCityRepository->addCity($samedayCity);
            }
        }

        $this->cacheHandler->refreshCachedData(
            SamedayConstants::TRANSIENT_CACHE_KEY_FOR_CITIES,
            $this->samedayCityRepository->getCities()
        );
    }
}
