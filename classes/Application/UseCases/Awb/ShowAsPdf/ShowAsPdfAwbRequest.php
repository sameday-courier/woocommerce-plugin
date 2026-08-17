<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\ShowAsPdf;

use SamedayCourier\Shipping\Domain\Ports\ShowAsPdfAwbServiceProviderInterface;

final class ShowAsPdfAwbRequest
{
    private ShowAsPdfAwbItem $showAsPdfAwbItem;

    private ShowAsPdfAwbServiceProviderInterface $showAsPdfAwbServiceProvider;

    public function __construct(
        ShowAsPdfAwbItem $showAsPdfAwbItem,
        ShowAsPdfAwbServiceProviderInterface $showAsPdfAwbServiceProvider
    ) {
        $this->showAsPdfAwbItem = $showAsPdfAwbItem;
        $this->showAsPdfAwbServiceProvider = $showAsPdfAwbServiceProvider;
    }

    public function getShowAsPdfAwbItem(): ShowAsPdfAwbItem
    {
        return $this->showAsPdfAwbItem;
    }

    public function getShowAsPdfAwbServiceProvider(): ShowAsPdfAwbServiceProviderInterface
    {
        return $this->showAsPdfAwbServiceProvider;
    }
}
