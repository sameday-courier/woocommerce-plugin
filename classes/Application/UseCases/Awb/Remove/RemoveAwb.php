<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\PostRemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveAwbRequestDto;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;
use SamedayCourier\Shipping\Domain\Ports\CourierServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\OrderAwbStoreServiceProviderInterface;
use SamedayCourier\Shipping\Domain\Ports\PostRemoveAwbServiceProviderInterface;

final class RemoveAwb
{
    private RemoveAwbItem $removeAwbItem;

    private OrderAwbStoreServiceProviderInterface $orderAwbStore;

    private CourierServiceProviderInterface $courierServiceProvider;

    private PostRemoveAwbServiceProviderInterface $postRemoveAwbServiceProvider;

    /**
     * @param RemoveAwbRequest $removeAwbRequest
     */
    public function __construct(RemoveAwbRequest $removeAwbRequest)
    {
        $this->removeAwbItem = $removeAwbRequest->getRemoveAwbItem();
        $this->orderAwbStore = $removeAwbRequest->getOrderAwbStore();
        $this->courierServiceProvider = $removeAwbRequest->getCourierServiceProvider();
        $this->postRemoveAwbServiceProvider = $removeAwbRequest->getPostRemoveAwbServiceProvider();
    }

    /**
     * @return RemoveAwbResponse
     */
    public function execute(): RemoveAwbResponse
    {
        $orderId = $this->removeAwbItem->getOrderId();
        $awb = $this->orderAwbStore->getByOrderId($orderId);

        if (null === $awb) {
            return new RemoveAwbResponse(
                "Invalid or inexistent an AWB for this OrderID: {$orderId}",
                ResponseNoticeType::ERROR
            );
        }

        try {
            $this->courierServiceProvider->removeAwb(
                new RemoveAwbRequestDto((string) $awb->getAwbNumber())
            );
        } catch (CourierServiceException $exception) {
            return new RemoveAwbResponse(
                $exception->getMessage(),
                ResponseNoticeType::ERROR
            );
        }

        $this->postRemoveAwbServiceProvider->apply(new PostRemoveAwbRequestDto($awb));

        return new RemoveAwbResponse(
            'Awb removed with success.',
            ResponseNoticeType::SUCCESS
        );
    }
}
