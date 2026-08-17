<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\AddNewParcel;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostParcelRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;

final class AddNewParcelAwb
{
    private AddNewParcelAwbItem $awbItem;

    private OrderAwbStoreServiceProviderInterface $orderAwbStore;

    private CourierServiceProviderInterface $courierServiceProvider;

    public function __construct(AddNewParcelAwbRequest $addNewParcelAwbRequest)
    {
        $this->awbItem = $addNewParcelAwbRequest->getAwbItem();
        $this->orderAwbStore = $addNewParcelAwbRequest->getOrderAwbStore();
        $this->courierServiceProvider = $addNewParcelAwbRequest->getCourierServiceProvider();
    }

    public function execute(): AddNewParcelAwbResponse
    {
        $orderId = $this->awbItem->getOrderId();
        $awb = $this->orderAwbStore->getByOrderId($orderId);

        if (null === $awb) {
            return new AddNewParcelAwbResponse(
                $orderId,
                'AWB not found for this order.',
                ResponseNoticeType::ERROR
            );
        }

        $position = $this->orderAwbStore->nextPosition($awb);

        try {
            $parcel = $this->courierServiceProvider->postParcel(
                new PostParcelRequestDto(
                    (string) $awb->getAwbNumber(),
                    $this->awbItem->getParcelWeight(),
                    $this->awbItem->getParcelWidth(),
                    $this->awbItem->getParcelLength(),
                    $this->awbItem->getParcelHeight(),
                    $position,
                    $this->awbItem->getParcelObservation(),
                    null,
                    $this->awbItem->isParcelIsLast()
                )
            );
        } catch (CourierServiceException $exception) {
            return new AddNewParcelAwbResponse(
                $orderId,
                $exception->getMessage(),
                ResponseNoticeType::ERROR
            );
        }

        if (!$this->orderAwbStore->appendParcel($awb, $position, $parcel->getParcelAwbNumber())) {
            return new AddNewParcelAwbResponse(
                $orderId,
                'Unable to update AWB parcels',
                ResponseNoticeType::ERROR
            );
        }

        return new AddNewParcelAwbResponse(
            $orderId,
            'AWB added new parcel successfully.',
            ResponseNoticeType::SUCCESS
        );
    }
}
