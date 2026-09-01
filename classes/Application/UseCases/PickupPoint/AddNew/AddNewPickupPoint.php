<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew;

use SamedayCourier\Shipping\Domain\DTOs\Requests\PostPickupPointRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class AddNewPickupPoint
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
     * @param AddNewPickupPointRequest $request
     * @return AddNewPickupPointResponse
     */
    public function execute(AddNewPickupPointRequest $request): AddNewPickupPointResponse
    {
        try {
            $this->courierServiceProvider->postPickupPoint(
                new PostPickupPointRequestDto(
                    $request->getPickupPointCountryId(),
                    $request->getPickupPointCountyId(),
                    $request->getPickupPointCityId(),
                    $request->getPickupPointAddress(),
                    $request->getPickupPointPostalCode(),
                    $request->getPickupPointAlias(),
                    [
                        [
                            'name' => $request->getPickupPointContactPersonName(),
                            'phone' => $request->getPickupPointContactPersonPhone(),
                            'default' => true,
                        ],
                    ],
                    $request->isDefault(),
                )
            );
        } catch (CourierServiceException $exception) {
            return new AddNewPickupPointResponse(
                $exception->getMessage(),
                true
            );
        }

        return new AddNewPickupPointResponse(
            'Successfully added new pickup point.',
            false
        );
    }
}
