<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowHistory;

use SamedayCourier\Shipping\Domain\Ports\ShowHistoryAwbServiceProviderInterface;

final class ShowHistoryAwbRequest
{
    private ShowHistoryAwbItem $showHistoryAwbItem;

    private ShowHistoryAwbServiceProviderInterface $showHistoryAwbServiceProvider;

    public function __construct(
        ShowHistoryAwbItem $showHistoryAwbItem,
        ShowHistoryAwbServiceProviderInterface $showHistoryAwbServiceProvider
    ) {
        $this->showHistoryAwbItem = $showHistoryAwbItem;
        $this->showHistoryAwbServiceProvider = $showHistoryAwbServiceProvider;
    }

    public function getShowHistoryAwbItem(): ShowHistoryAwbItem
    {
        return $this->showHistoryAwbItem;
    }

    public function getShowHistoryAwbServiceProvider(): ShowHistoryAwbServiceProviderInterface
    {
        return $this->showHistoryAwbServiceProvider;
    }
}
