<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use SamedayCourier\Shipping\Domain\DTOs\Requests\ShowHistoryAwbRequestDto;
use SamedayCourier\Shipping\Domain\Ports\ShowHistoryAwbServiceProviderInterface;

final class ShowHistoryAwb
{
    private ShowHistoryAwbItem $showHistoryAwbItem;

    private ShowHistoryAwbServiceProviderInterface $showHistoryAwbServiceProvider;

    public function __construct(ShowHistoryAwbRequest $showHistoryAwbRequest)
    {
        $this->showHistoryAwbItem = $showHistoryAwbRequest->getShowHistoryAwbItem();
        $this->showHistoryAwbServiceProvider = $showHistoryAwbRequest->getShowHistoryAwbServiceProvider();
    }

    public function execute(): ShowHistoryAwbResponse
    {
        $showHistoryAwbResponse = $this->showHistoryAwbServiceProvider->showHistory(
            new ShowHistoryAwbRequestDto($this->showHistoryAwbItem->getOrderId())
        );

        return new ShowHistoryAwbResponse(
            $showHistoryAwbResponse->getOrderId(),
            $showHistoryAwbResponse->isSuccess(),
            $showHistoryAwbResponse->getPackages(),
        );
    }
}
