<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate;

use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;

final class StartBulkGenerateAwbRequest
{
    private StartBulkGenerateAwbItem $startBulkGenerateAwbItem;

    private BulkJobStoreInterface $bulkJobStore;

    public function __construct(
        StartBulkGenerateAwbItem $startBulkGenerateAwbItem,
        BulkJobStoreInterface $bulkJobStore
    ) {
        $this->startBulkGenerateAwbItem = $startBulkGenerateAwbItem;
        $this->bulkJobStore = $bulkJobStore;
    }

    public function getStartBulkGenerateAwbItem(): StartBulkGenerateAwbItem
    {
        return $this->startBulkGenerateAwbItem;
    }

    public function getBulkJobStore(): BulkJobStoreInterface
    {
        return $this->bulkJobStore;
    }
}
