<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Infrastructure\Wordpress\Services;

use Sameday\Objects\PickupPoint\PickupPointContactPersonObject;
use SamedayCourier\Shipping\Domain\DTOs\Requests\AddNewPickupPointServiceRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostPickupPointRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Responses\AddNewPickupPointServiceResponseDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\AddNewPickupPointServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class AddNewPickupPointServiceProvider implements AddNewPickupPointServiceProviderInterface
{
    private CourierServiceProviderInterface $courier;

    public function __construct(?CourierServiceProviderInterface $courier = null)
    {
        $this->courier = $courier ?? new CourierServiceProvider();
    }

    /**
     * @param AddNewPickupPointServiceRequestDto $addNewPickupPointServiceRequestDto
     *
     * @return AddNewPickupPointServiceResponseDto
     */
    public function add(
        AddNewPickupPointServiceRequestDto $addNewPickupPointServiceRequestDto
    ): AddNewPickupPointServiceResponseDto {
        try {
            $this->courier->postPickupPoint(
                new PostPickupPointRequestDto(
                    $addNewPickupPointServiceRequestDto->getPickupPointCountryId(),
                    $addNewPickupPointServiceRequestDto->getPickupPointCountyId(),
                    $addNewPickupPointServiceRequestDto->getPickupPointCityId(),
                    $addNewPickupPointServiceRequestDto->getPickupPointAddress(),
                    $addNewPickupPointServiceRequestDto->getPickupPointPostalCode(),
                    $addNewPickupPointServiceRequestDto->getPickupPointAlias(),
                    [
                        new PickupPointContactPersonObject(
                            $addNewPickupPointServiceRequestDto->getPickupPointContactPersonName(),
                            $addNewPickupPointServiceRequestDto->getPickupPointContactPersonPhone(),
                            true,
                        ),
                    ],
                    $addNewPickupPointServiceRequestDto->isDefault(),
                )
            );
        } catch (CourierServiceException $exception) {
            return new AddNewPickupPointServiceResponseDto(
                false,
                $exception->getMessage()
            );
        }

        return new AddNewPickupPointServiceResponseDto(
            true,
            'Successfully added new pickup point.'
        );
    }
}
