<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday;

use SamedayCourier\Shipping\Domain\Models\SamedayCity;
use SamedayCourier\Shipping\Domain\SamedayConstants;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayCityMapper;
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
        return $this->dbHandler->buildTableName(self::TABLE_NAME);
    }

    /**
     * @return array<string, SamedayCity[]>
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
     * @return array<string, SamedayCity[]>
     */
    public function getCities(): array
    {
        $mapper = $this->getMapper(SamedayCityMapper::class);
        $cities = [];
        foreach (SamedayConstants::DEFAULT_COUNTRIES as $countryKey => $value) {
            $rows = $this->dbHandler->getRows(
                "SELECT * FROM %s WHERE country_code = %s",
                [
                    $this->getTableName(),
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
        $row = $this->dbHandler->getRow(
            "SELECT postal_code FROM %s WHERE county_code = %s AND country_code = %s LIMIT 1",
            [
                $this->getTableName(),
                $countyCode,
                $countryCode,
            ]
        );

        return isset($row['postal_code']) ? (string) $row['postal_code'] : null;
    }
}
