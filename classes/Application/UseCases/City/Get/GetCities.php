<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCitiesRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class GetCities
{
    private GetCitiesItem $getCitiesItem;

    private CourierServiceProviderInterface $courierServiceProvider;

    /**
     * @param GetCitiesRequest $getCitiesRequest
     */
    public function __construct(GetCitiesRequest $getCitiesRequest)
    {
        $this->getCitiesItem = $getCitiesRequest->getGetCitiesItem();
        $this->courierServiceProvider = $getCitiesRequest->getCourierServiceProvider();
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
                $cities = $this->courierServiceProvider->getCities(
                    new GetCitiesRequestDto(
                        $this->getCitiesItem->getCountyId(),
                        null,
                        null,
                        $page++
                    )
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
            $remoteCities,
        );
    }
}
