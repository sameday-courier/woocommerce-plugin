<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use Sameday\Objects\CityObject;
use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCitiesRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class GetCities
{
    private CourierServiceProviderInterface $courier;

    /**
     * @var int $countyId
     */
    private int $countyId;

    public function __construct(GetCitiesRequest $getCitiesRequest)
    {
        $this->courier = $getCitiesRequest->getCourier();
        $this->countyId = $getCitiesRequest->getGetCitiesItem()->getCountyId();
    }

    /**
     * @return GetCitiesResponse
     */
    public function execute(): GetCitiesResponse
    {
        $page = 1;
        $remoteCities = [];

        do {
            try {
                $cities = $this->courier->getCities(
                    new GetCitiesRequestDto($this->countyId, null, null, $page++)
                );
            } catch (CourierServiceException $exception) {
                return new GetCitiesResponse(
                    $exception->getMessage(),
                    ResponseNoticeType::ERROR,
                );
            }

            foreach ($cities->getCities() as $city) {
                $remoteCities[] = $city;
            }
        } while ($page <= $cities->getPages());

        return new GetCitiesResponse(
            null,
            ResponseNoticeType::SUCCESS,
            array_map(
                static function (CityObject $city): array {
                    return [
                        'id' => $city->getId(),
                        'name' => $city->getName(),
                    ];
                },
                $remoteCities
            ),
        );
    }
}
