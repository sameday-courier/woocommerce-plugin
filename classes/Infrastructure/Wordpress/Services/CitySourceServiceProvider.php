<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use SamedayCourier\Shipping\Domain\DTOs\CitySourceDto;
use SamedayCourier\Shipping\Domain\Ports\CitySourceProviderInterface;
use SamedayCourier\Shipping\Infrastructure\Common\Services\FileReadHandler;
use stdClass;

final class CitySourceServiceProvider implements CitySourceProviderInterface
{
    /**
     * @return CitySourceDto[]|null
     */
    public function readCities(): ?array
    {
        $cities = FileReadHandler::readJsonFile('cities');
        if (null === $cities) {
            return null;
        }

        return array_map(
            static function (stdClass $city): CitySourceDto {
                return new CitySourceDto(
                    (int) $city->city_id,
                    (string) $city->city_name,
                    (string) $city->county_code,
                    (string) $city->postal_code,
                    (string) $city->country_code
                );
            },
            $cities
        );
    }
}
