<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday;

use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Sql\Repository\AbstractRepository;
use stdClass;

if (!defined('ABSPATH')) {
    exit;
}

class SamedayCityRepository extends AbstractRepository
{
    private const TABLE_NAME = 'sameday_cities';

    public function getTableName(): string
    {
        $this->dbHandler->buildTableName(self::TABLE_NAME);
    }

    /**
     * @return array
     */
    public function getCachedCities(): array
    {
        if (false === $cities = get_transient(SamedayConstants::TRANSIENT_CACHE_KEY_FOR_CITIES)) {
            $cities = $this->getCities();
            set_transient(
                SamedayConstants::TRANSIENT_CACHE_KEY_FOR_CITIES,
                $cities,
                31556926
            );
        }

        return $cities;
    }

    /**
     * @return array
     */
    public function getCities(): array
    {
        $cities = [];
        foreach (SamedayConstants::DEFAULT_COUNTRIES as $countryKey => $value) {
            $queryString = $this->dbHandler->prepareQuery(
                "SELECT city_name, county_code FROM %s WHERE country_code = %s",
                [
                    $this->getTableName(),
                    $countryKey,
                ]
            );

            $cities[$countryKey] = $this->dbHandler->getRows($queryString);
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
        if ($cityObject->country_code === 'BG') {
            $countyCode = 'BG-' . $cityObject->county_code;
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
        $queryString = $this->dbHandler->prepareQuery(
            "SELECT postal_code FROM %s WHERE county_code = %s AND country_code = %s LIMIT 1",
            [
                $this->getTableName(),
                $countyCode,
                $countryCode,
            ]
        );

        $row = $this->dbHandler->getRow($queryString);

        return isset($row['postal_code']) ? (string) $row['postal_code'] : null;
    }
}
