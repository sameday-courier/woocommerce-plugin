<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Sql\Repository\Sameday;

use SamedayCourier\Shipping\Infrastructure\Sql\Repository\RepositoryInterface;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Services\DbHandler;
use SamedayCourier\Shipping\Utils\Helper;
use stdClass;

if (!defined('ABSPATH')) {
    exit;
}

class SamedayCityRepository implements RepositoryInterface
{
    private const TABLE_NAME = 'sameday_cities';

    public static function getTableName(): string
    {
        return DbHandler::buildTableName(self::TABLE_NAME);
    }

    /**
     * @return array
     */
    public static function getCachedCities(): array
    {
        if (false === $cities = get_transient(Helper::TRANSIENT_CACHE_KEY_FOR_CITIES)) {
            $cities = self::getCities();
            set_transient(
                Helper::TRANSIENT_CACHE_KEY_FOR_CITIES,
                $cities,
                31556926
            );
        }

        return $cities;
    }

    /**
     * @return array
     */
    public static function getCities(): array
    {
        $cities = [];
        foreach (Helper::DEFAULT_COUNTRIES as $countryKey => $value) {
            $queryString = DbHandler::prepareQuery(
                "SELECT city_name, county_code FROM %s WHERE country_code = %s",
                [
                    self::getTableName(),
                    $countryKey,
                ]
            );

            $cities[$countryKey] = DbHandler::getRows($queryString);
        }

        return $cities;
    }

    /**
     * @return void
     */
    public static function truncate(): void
    {
        DbHandler::truncateTable(self::getTableName());
    }

    /**
     * @param stdClass $cityObject
     *
     * @return void
     */
    public static function addCity(stdClass $cityObject): void
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

        DbHandler::insertRow(self::getTableName(), $data);
    }

    /**
     * @param string $countyCode
     * @param string $countryCode
     *
     * @return string|null
     */
    public static function getPostalForSpecificCounty(string $countyCode, string $countryCode): ?string
    {
        $queryString = DbHandler::prepareQuery(
            "SELECT postal_code FROM %s WHERE county_code = %s AND country_code = %s LIMIT 1",
            [
                self::getTableName(),
                $countyCode,
                $countryCode,
            ]
        );

        $row = DbHandler::getRow($queryString);

        return isset($row['postal_code']) ? (string) $row['postal_code'] : null;
    }

    /**
     * @return void
     */
    public static function createTable(): void
    {
        $tableName = self::getTableName();
        $collate = DbHandler::getCharsetCollate();

        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id INT(11) NOT NULL AUTO_INCREMENT,
            city_id INT(11),
            city_name VARCHAR(255),
            county_code VARCHAR(255),
            postal_code VARCHAR(10),
            country_code VARCHAR(10),
            PRIMARY KEY (id)
        ) $collate;";

        DbHandler::executeQuery($sql);
    }
}
