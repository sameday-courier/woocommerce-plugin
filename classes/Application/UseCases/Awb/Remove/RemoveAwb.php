<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\Remove;

use SamedayCourier\Shipping\Application\Common\ResponseNoticeType\ResponseNoticeType;
use SamedayCourier\Shipping\Domain\DTOs\Requests\RemoveOrderAwbRequestDto;
use SamedayCourier\Shipping\Domain\Ports\RemoveOrderAwbServiceProviderInterface;

final class RemoveAwb
{
    private RemoveAwbItem $removeAwbItem;

    private RemoveOrderAwbServiceProviderInterface $removeOrderAwbServiceProvider;

    public function __construct(RemoveAwbRequest $removeAwbRequest)
    {
        $this->removeAwbItem = $removeAwbRequest->getRemoveAwbItem();
        $this->removeOrderAwbServiceProvider = $removeAwbRequest->getRemoveOrderAwbServiceProvider();
    }

    public function execute(): RemoveAwbResponse
    {
        $removeOrderAwbResponse = $this->removeOrderAwbServiceProvider->remove(
            new RemoveOrderAwbRequestDto($this->removeAwbItem->getOrderId())
        );

        return new RemoveAwbResponse(
            $removeOrderAwbResponse->getMessage(),
            $removeOrderAwbResponse->isSuccess()
                ? ResponseNoticeType::SUCCESS
                : ResponseNoticeType::ERROR,
        );
    }
}
