<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\PickupPoint\AddNew;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\AddNewPickupPointServiceRequestDto;
use SamedayCourier\Shipping\Domain\Ports\AddNewPickupPointServiceProviderInterface;

final class AddNewPickupPoint
{
    private AddNewPickupPointItem $addNewPickupPointItem;

    private AddNewPickupPointServiceProviderInterface $addNewPickupPointServiceProvider;

    public function __construct(AddNewPickupPointRequest $addNewPickupPointRequest)
    {
        $this->addNewPickupPointItem = $addNewPickupPointRequest->getAddNewPickupPointItem();
        $this->addNewPickupPointServiceProvider = $addNewPickupPointRequest->getAddNewPickupPointServiceProvider();
    }

    public function execute(): AddNewPickupPointResponse
    {
        $response = $this->addNewPickupPointServiceProvider->add(
            new AddNewPickupPointServiceRequestDto(
                $this->addNewPickupPointItem->getPickupPointCountryId(),
                $this->addNewPickupPointItem->getPickupPointCountyId(),
                $this->addNewPickupPointItem->getPickupPointCityId(),
                $this->addNewPickupPointItem->getPickupPointAddress(),
                $this->addNewPickupPointItem->getPickupPointPostalCode(),
                $this->addNewPickupPointItem->getPickupPointAlias(),
                $this->addNewPickupPointItem->getPickupPointContactPersonName(),
                $this->addNewPickupPointItem->getPickupPointContactPersonPhone(),
                $this->addNewPickupPointItem->isDefault(),
            )
        );

        return new AddNewPickupPointResponse(
            $response->getMessage(),
            $response->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
