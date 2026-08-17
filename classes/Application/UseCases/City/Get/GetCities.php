<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCitiesServiceRequestDto;
use SamedayCourier\Shipping\Domain\Ports\GetCitiesServiceProviderInterface;

final class GetCities
{
    private GetCitiesItem $getCitiesItem;

    private GetCitiesServiceProviderInterface $getCitiesServiceProvider;

    public function __construct(GetCitiesRequest $getCitiesRequest)
    {
        $this->getCitiesItem = $getCitiesRequest->getGetCitiesItem();
        $this->getCitiesServiceProvider = $getCitiesRequest->getGetCitiesServiceProvider();
    }

    /**
     * @return GetCitiesResponse
     */
    public function execute(): GetCitiesResponse
    {
        $response = $this->getCitiesServiceProvider->get(
            new GetCitiesServiceRequestDto($this->getCitiesItem->getCountyId())
        );

        if (!$response->isSuccess()) {
            return new GetCitiesResponse(
                $response->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new GetCitiesResponse(
            null,
            ResponseNoticeType::SUCCESS,
            $response->getCities(),
        );
    }
}
