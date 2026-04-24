<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Services\Mappers;

if (!defined( 'ABSPATH')) {
    exit;
}

use SamedayCourier\Shipping\Domain\Models\SamedayCity;

final class SamedayCityMapper extends AbstractMapper
{
    /**
     * @param array $row
     *
     * @return SamedayCity
     */
    public function map(array $row): SamedayCity
    {
        $city = new SamedayCity();

        $city->setId(isset($row['id']) ? (int) $row['id'] : 0);
        $city->setCityId(
            isset($row['city_id']) && $row['city_id'] !== '' && $row['city_id'] !== null
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
