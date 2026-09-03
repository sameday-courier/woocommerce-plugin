<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\City\Get;

use SamedayCourier\Shipping\Application\Common\AbstractUseCase;
use SamedayCourier\Shipping\Application\Common\Interfaces\RequestInterface;
use SamedayCourier\Shipping\Domain\DTOs\Requests\GetCitiesRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

/**
 * @extends AbstractUseCase<GetCitiesRequest, GetCitiesResponse>
 *
 * @method GetCitiesResponse execute(GetCitiesRequest $request)
 */
final class GetCities extends AbstractUseCase
{
    /**
     * @var CourierServiceProviderInterface $courierServiceProvider
     */
    private CourierServiceProviderInterface $courierServiceProvider;

    /**
     * @param CourierServiceProviderInterface $courierServiceProvider
     */
    public function __construct(
        CourierServiceProviderInterface $courierServiceProvider
    ) {
        $this->courierServiceProvider = $courierServiceProvider;
    }

    /**
     * @param GetCitiesRequest $request
     * @return GetCitiesResponse
     */
    protected function processAction(RequestInterface $request): GetCitiesResponse
    {
        $page = 1;
        $remoteCities = [];

        do {
            try {
                $cities = $this->courierServiceProvider->getCities(
                    new GetCitiesRequestDto(
                        $request->getCountyId(),
                        null,
                        null,
                        $page++
                    )
                );
            } catch (CourierServiceException $exception) {
                return new GetCitiesResponse(
                    $exception->getMessage(),
                    true
                );
            }

            foreach ($cities->getCities() as $city) {
                $remoteCities[] = $city;
            }
        } while ($page <= $cities->getPages());

        return new GetCitiesResponse(
            '',
            false,
            $remoteCities
        );
    }
}
