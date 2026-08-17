<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate;

use SamedayCourier\Shipping\Domain\Ports\StartBulkGenerateAwbServiceProviderInterface;

final class StartBulkGenerateAwbRequest
{
    private StartBulkGenerateAwbItem $startBulkGenerateAwbItem;

    private StartBulkGenerateAwbServiceProviderInterface $startBulkGenerateAwbServiceProvider;

    public function __construct(
        StartBulkGenerateAwbItem $startBulkGenerateAwbItem,
        StartBulkGenerateAwbServiceProviderInterface $startBulkGenerateAwbServiceProvider
    ) {
        $this->startBulkGenerateAwbItem = $startBulkGenerateAwbItem;
        $this->startBulkGenerateAwbServiceProvider = $startBulkGenerateAwbServiceProvider;
    }

    public function getStartBulkGenerateAwbItem(): StartBulkGenerateAwbItem
    {
        return $this->startBulkGenerateAwbItem;
    }

    public function getStartBulkGenerateAwbServiceProvider(): StartBulkGenerateAwbServiceProviderInterface
    {
        return $this->startBulkGenerateAwbServiceProvider;
    }
}
