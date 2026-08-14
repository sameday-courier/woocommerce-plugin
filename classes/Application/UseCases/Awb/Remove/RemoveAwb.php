<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Application\Common\Services\AwbRemover;
use SamedayCourier\Shipping\Domain\Exceptions\AwbNotFoundForOrderException;
use SamedayCourier\Shipping\Domain\Exceptions\CourierServiceException;

final class RemoveAwb
{
    /**
     * @var RemoveAwbItem $removeAwbItem
     */
    private RemoveAwbItem $removeAwbItem;

    /**
     * @var AwbRemover $awbRemover
     */
    private AwbRemover $awbRemover;

    /**
     * @param RemoveAwbRequest $removeAwbRequest
     */
    public function __construct(
        RemoveAwbRequest $removeAwbRequest
    ) {
        $this->removeAwbItem = $removeAwbRequest->getRemoveAwbItem();
        $this->awbRemover = $removeAwbRequest->getAwbRemover();
    }

    /**
     * @return RemoveAwbResponse
     */
    public function execute(): RemoveAwbResponse
    {
        try {
            $this->awbRemover->remove($this->removeAwbItem->getOrderId());
        } catch (AwbNotFoundForOrderException $exception) {
            return new RemoveAwbResponse(
                "Invalid or inexistent an AWB for this OrderID: {$exception->getOrderId()}",
                ResponseNoticeType::ERROR,
            );
        } catch (CourierServiceException $exception) {
            return new RemoveAwbResponse(
                $exception->getMessage(),
                ResponseNoticeType::ERROR,
            );
        }

        return new RemoveAwbResponse(
            "Awb removed with success.",
            ResponseNoticeType::SUCCESS,
        );
    }
}
