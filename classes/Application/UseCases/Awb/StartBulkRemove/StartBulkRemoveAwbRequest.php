<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove;

use SamedayCourier\Shipping\Domain\Ports\StartBulkRemoveAwbServiceProviderInterface;

final class StartBulkRemoveAwbRequest
{
    private StartBulkRemoveAwbItem $startBulkRemoveAwbItem;

    private StartBulkRemoveAwbServiceProviderInterface $startBulkRemoveAwbServiceProvider;

    public function __construct(
        StartBulkRemoveAwbItem $startBulkRemoveAwbItem,
        StartBulkRemoveAwbServiceProviderInterface $startBulkRemoveAwbServiceProvider
    ) {
        $this->startBulkRemoveAwbItem = $startBulkRemoveAwbItem;
        $this->startBulkRemoveAwbServiceProvider = $startBulkRemoveAwbServiceProvider;
    }

    public function getStartBulkRemoveAwbItem(): StartBulkRemoveAwbItem
    {
        return $this->startBulkRemoveAwbItem;
    }

    public function getStartBulkRemoveAwbServiceProvider(): StartBulkRemoveAwbServiceProviderInterface
    {
        return $this->startBulkRemoveAwbServiceProvider;
    }
}
