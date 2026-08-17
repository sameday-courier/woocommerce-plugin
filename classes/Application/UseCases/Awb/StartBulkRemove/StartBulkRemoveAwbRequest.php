<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove;

use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;

final class StartBulkRemoveAwbRequest
{
    private StartBulkRemoveAwbItem $startBulkRemoveAwbItem;

    private BulkJobStoreInterface $bulkJobStore;

    public function __construct(
        StartBulkRemoveAwbItem $startBulkRemoveAwbItem,
        BulkJobStoreInterface $bulkJobStore
    ) {
        $this->startBulkRemoveAwbItem = $startBulkRemoveAwbItem;
        $this->bulkJobStore = $bulkJobStore;
    }

    public function getStartBulkRemoveAwbItem(): StartBulkRemoveAwbItem
    {
        return $this->startBulkRemoveAwbItem;
    }

    public function getBulkJobStore(): BulkJobStoreInterface
    {
        return $this->bulkJobStore;
    }
}
