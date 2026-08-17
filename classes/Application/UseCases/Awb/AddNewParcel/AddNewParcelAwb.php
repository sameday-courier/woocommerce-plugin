<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\AddNewParcelAwbRequestDto;
use SamedayCourier\Shipping\Domain\Ports\AddNewParcelAwbServiceProviderInterface;

final class AddNewParcelAwb
{
    private AddNewParcelAwbItem $awbItem;

    private AddNewParcelAwbServiceProviderInterface $addNewParcelAwbServiceProvider;

    public function __construct(AddNewParcelAwbRequest $addNewParcelAwbRequest)
    {
        $this->awbItem = $addNewParcelAwbRequest->getAwbItem();
        $this->addNewParcelAwbServiceProvider = $addNewParcelAwbRequest->getAddNewParcelAwbServiceProvider();
    }

    public function execute(): AddNewParcelAwbResponse
    {
        $addNewParcelAwbResponse = $this->addNewParcelAwbServiceProvider->add(
            new AddNewParcelAwbRequestDto(
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
            $addNewParcelAwbResponse->getOrderId(),
            $addNewParcelAwbResponse->getMessage(),
            $addNewParcelAwbResponse->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
