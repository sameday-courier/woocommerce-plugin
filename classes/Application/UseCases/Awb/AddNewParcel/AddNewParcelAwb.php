<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\AddNewParcelRequestDto;
use SamedayCourier\Shipping\Domain\Ports\AddNewParcelServiceProviderInterface;

final class AddNewParcelAwb
{
    private AddNewParcelAwbItem $awbItem;

    private AddNewParcelServiceProviderInterface $addNewParcelServiceProvider;

    public function __construct(AddNewParcelAwbRequest $addNewParcelAwbRequest)
    {
        $this->awbItem = $addNewParcelAwbRequest->getAwbItem();
        $this->addNewParcelServiceProvider = $addNewParcelAwbRequest->getAddNewParcelServiceProvider();
    }

    public function execute(): AddNewParcelAwbResponse
    {
        $addNewParcelResponse = $this->addNewParcelServiceProvider->add(
            new AddNewParcelRequestDto(
                $this->awbItem->getOrderId(),
                $this->awbItem->getParcelWeight(),
                $this->awbItem->getParcelWidth(),
                $this->awbItem->getParcelLength(),
                $this->awbItem->getParcelHeight(),
                $this->awbItem->getParcelObservation(),
                $this->awbItem->isParcelIsLast()
            )
        );

        return new AddNewParcelAwbResponse(
            $addNewParcelResponse->getOrderId(),
            $addNewParcelResponse->getMessage(),
            $addNewParcelResponse->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
