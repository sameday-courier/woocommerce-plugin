<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

use SamedayCourier\Shipping\Domain\Models\CarrierCity;

/**
 * @extends AbstractMapper<CarrierCity>
 */
final class SamedayCityMapper extends AbstractMapper
{
    /**
     * @param array $row
     *
     * @return CarrierCity
     */
    public function map(array $row): CarrierCity
    {
        $city = new CarrierCity();

        $city->setId(isset($row['id']) ? (int) $row['id'] : 0);
        $city->setCityId(
            isset($row['city_id']) && $row['city_id'] !== ''
                ? (int) $row['city_id']
                : null
        );
        $city->setCityName($row["city_name"] ?? null);
        $city->setCountyCode($row["county_code"] ?? null);
        $city->setPostalCode($row["postal_code"] ?? null);
        $city->setCountryCode($row["country_code"] ?? null);

        return $city;
    }
}
