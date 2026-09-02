<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\Sameday;

use SamedayCourier\Shipping\Domain\DTOs\CitySourceDto;
use SamedayCourier\Shipping\Domain\Models\CarrierCity;
use SamedayCourier\Shipping\Domain\Ports\CityPostalCodeProviderInterface;
use SamedayCourier\Shipping\Domain\CarrierConstants;
use SamedayCourier\Shipping\Infrastructure\Services\Mappers\SamedayCityMapper;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Sql\Repository\AbstractRepository;
use SamedayCourier\Shipping\Infrastructure\Wordpress\Handlers\CacheHandler;
use RuntimeException;
use Throwable;

class SamedayCityRepository extends AbstractRepository implements CityPostalCodeProviderInterface
{
    private const TABLE_NAME = 'sameday_cities';

    private const CITIES_CACHE_TTL_SECONDS = 31556926;

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->dbHandler->buildTableName(self::TABLE_NAME);
    }

    /**
     * @return array<string, array<int, CarrierCity>>
     */
    public function getCachedCities(): array
    {
        $cacheHandler = new CacheHandler();
        $cacheKey = CarrierConstants::TRANSIENT_CACHE_KEY_FOR_CITIES;

        try {
            $cities = $cacheHandler->getCachedData($cacheKey);

            if ([] !== $cities && !$this->hasValidCachedCities($cities)) {
                throw new RuntimeException('Cached cities payload is invalid.');
            }
        } catch (Throwable $exception) {
            $cacheHandler->invalidateCachedData($cacheKey);
            $cities = [];
        }

        if ([] === $cities) {
            $cities = $this->getCities();
            $cacheHandler->refreshCachedData(
                $cacheKey,
                $cities,
                self::CITIES_CACHE_TTL_SECONDS
            );
        }

        return $cities;
    }

    /**
     * @return array<string, array<int, CarrierCity>>
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
     * @param CitySourceDto $city
     *
     * @return void
     */
    public function addCity(CitySourceDto $city): void
    {
        $countyCode = $city->getCountyCode();
        if ($city->getCountryCode() === CarrierConstants::API_HOST_LOCALE_BG) {
            $countyCode = CarrierConstants::API_HOST_LOCALE_BG . "-" . $city->getCountyCode();
        }

        $data = [
            'city_id' => $city->getCityId(),
            'city_name' => $city->getCityName(),
            'county_code' => $countyCode,
            'postal_code' => $city->getPostalCode(),
            'country_code' => $city->getCountryCode(),
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

    /**
     * @param array $cities
     *
     * @return bool
     */
    private function hasValidCachedCities(array $cities): bool
    {
        foreach ($cities as $cityModels) {
            if (!is_array($cityModels)) {
                return false;
            }

            if ([] === $cityModels) {
                continue;
            }

            $city = reset($cityModels);
            if (!$city instanceof CarrierCity) {
                return false;
            }
        }

        return true;
    }
}
