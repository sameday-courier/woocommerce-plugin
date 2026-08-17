<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostPickupPointRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;

final class AddNewPickupPoint
{
    private AddNewPickupPointItem $addNewPickupPointItem;

    private CourierServiceProviderInterface $courierServiceProvider;

    public function __construct(AddNewPickupPointRequest $addNewPickupPointRequest)
    {
        $this->addNewPickupPointItem = $addNewPickupPointRequest->getAddNewPickupPointItem();
        $this->courierServiceProvider = $addNewPickupPointRequest->getCourierServiceProvider();
    }

    public function execute(): AddNewPickupPointResponse
    {
        try {
            $this->courierServiceProvider->postPickupPoint(
                new PostPickupPointRequestDto(
                    $this->addNewPickupPointItem->getPickupPointCountryId(),
                    $this->addNewPickupPointItem->getPickupPointCountyId(),
                    $this->addNewPickupPointItem->getPickupPointCityId(),
                    $this->addNewPickupPointItem->getPickupPointAddress(),
                    $this->addNewPickupPointItem->getPickupPointPostalCode(),
                    $this->addNewPickupPointItem->getPickupPointAlias(),
                    [
                        [
                            'name' => $this->addNewPickupPointItem->getPickupPointContactPersonName(),
                            'phone' => $this->addNewPickupPointItem->getPickupPointContactPersonPhone(),
                            'default' => true,
                        ],
                    ],
                    $this->addNewPickupPointItem->isDefault(),
                )
            );
        } catch (CourierServiceException $exception) {
            return new AddNewPickupPointResponse(
                $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new AddNewPickupPointResponse(
            'Successfully added new pickup point.',
            ResponseNoticeType::SUCCESS,
        );
    }
}
