<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Sameday\Objects\CityObject;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCitiesRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCitiesServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\GetCitiesServiceResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\GetCitiesServiceProviderInterface;

final class GetCitiesServiceProvider implements GetCitiesServiceProviderInterface
{
    private CourierServiceProviderInterface $courier;

    public function __construct(?CourierServiceProviderInterface $courier = null)
    {
        $this->courier = $courier ?? new CourierServiceProvider();
    }

    /**
     * @param GetCitiesServiceRequestDto $getCitiesServiceRequestDto
     *
     * @return GetCitiesServiceResponseDto
     */
    public function get(GetCitiesServiceRequestDto $getCitiesServiceRequestDto): GetCitiesServiceResponseDto
    {
        $page = 1;
        $remoteCities = [];

        do {
            try {
                $cities = $this->courier->getCities(
                    new GetCitiesRequestDto(
                        $getCitiesServiceRequestDto->getCountyId(),
                        null,
                        null,
                        $page++
                    )
                );
            } catch (CourierServiceException $exception) {
                return new GetCitiesServiceResponseDto(
                    false,
                    $exception->getMessage()
                );
            }

            foreach ($cities->getCities() as $city) {
                $remoteCities[] = $city;
            }
        } while ($page <= $cities->getPages());

        return new GetCitiesServiceResponseDto(
            true,
            'Cities retrieved successfully.',
            array_map(
                static function (CityObject $city): array {
                    return [
                        'id' => $city->getId(),
                        'name' => $city->getName(),
                    ];
                },
                $remoteCities
            )
        );
    }
}
