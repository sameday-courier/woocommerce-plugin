<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Refresh;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\CitiesRefreshRequestDto;
use SamedayCourier\Shipping\Domain\Ports\CitiesServiceProviderInterface;

final class RefreshCity
{
    /**
     * @var CitiesServiceProviderInterface $citiesServiceProvider
     */
    private CitiesServiceProviderInterface $citiesServiceProvider;

    /**
     * @param RefreshCityRequest $refreshCitiesRequest
     */
    public function __construct(RefreshCityRequest $refreshCitiesRequest)
    {
        $this->citiesServiceProvider = $refreshCitiesRequest->getCitiesServiceProvider();
    }

    /**
     * @return RefreshCityResponse
     */
    public function execute(): RefreshCityResponse
    {
        $citiesRefreshResponse = $this->citiesServiceProvider->refresh(
            new CitiesRefreshRequestDto()
        );

        return new RefreshCityResponse(
            $citiesRefreshResponse->getMessage(),
            $citiesRefreshResponse->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
