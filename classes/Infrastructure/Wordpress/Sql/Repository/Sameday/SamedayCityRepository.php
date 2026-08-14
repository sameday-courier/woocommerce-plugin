<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday;

use SamedayCourier\Shipping\Domain\Models\CarrierCity;
use SamedayCourier\Shipping\Domain\Ports\CityPostalCodeProviderInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayCityMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\AbstractRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\CacheHandler;
use stdClass;

class SamedayCityRepository extends AbstractRepository implements CityPostalCodeProviderInterface
{
    private const TABLE_NAME = 'sameday_cities';

    public function getTableName(): string
    {
        return $this->dbHandler->buildTableName(self::TABLE_NAME);
    }

    /**
     * @return array<string, CarrierCity[]>
     */
    public function getCachedCities(): array
    {
        $cacheHandler = new CacheHandler();
        $cities = $cacheHandler->getCachedData(CarrierConstants::TRANSIENT_CACHE_KEY_FOR_CITIES);

        if ([] === $cities) {
            $cities = $this->getCities();
            $cacheHandler->refreshCachedData(
                CarrierConstants::TRANSIENT_CACHE_KEY_FOR_CITIES,
                $cities,
                31556926
            );
        }

        return $cities;
    }

    /**
     * @return array<string, CarrierCity[]>
     */
    public function getCities(): array
    {
        $mapper = $this->getMapper(SamedayCityMapper::class);
        $cities = [];
        foreach (CarrierConstants::DEFAULT_COUNTRIES as $countryKey => $value) {
            $rows = $this->dbHandler->getRows(
                "SELECT * FROM {$this->getTableName()} WHERE country_code = %s",
                [
                    $countryKey,
                ]
            );
            $cities[$countryKey] = $mapper->mapCollection($rows);
        }

        return $cities;
    }

    /**
     * @return void
     */
    public function truncate(): void
    {
        $this->dbHandler->truncateTable($this->getTableName());
    }

    /**
     * @param stdClass $cityObject
     *
     * @return void
     */
    public function addCity(stdClass $cityObject): void
    {
        $countyCode = $cityObject->county_code;
        if ($cityObject->country_code === CarrierConstants::API_HOST_LOCALE_BG) {
            $countyCode = CarrierConstants::API_HOST_LOCALE_BG . "-" . $cityObject->county_code;
        }

        $data = [
            'city_id' => $cityObject->city_id,
            'city_name' => $cityObject->city_name,
            'county_code' => $countyCode,
            'postal_code' => $cityObject->postal_code,
            'country_code' => $cityObject->country_code,
        ];

        $this->dbHandler->insertRow($this->getTableName(), $data);
    }

    /**
     * @param string $countyCode
     * @param string $countryCode
     *
     * @return string|null
     */
    public function getPostalForSpecificCounty(string $countyCode, string $countryCode): ?string
    {
        $row = $this->dbHandler->getRow(
            "SELECT postal_code FROM {$this->getTableName()} WHERE county_code = %s AND country_code = %s LIMIT 1",
            [
                $countyCode,
                $countryCode,
            ]
        );

        return isset($row['postal_code']) ? (string) $row['postal_code'] : null;
    }
}
