<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate;

use SamedayCourier\Shipping\Domain\Ports\BulkJobIdGeneratorInterface;
use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;

final class StartBulkGenerateAwbRequest
{
    private StartBulkGenerateAwbItem $startBulkGenerateAwbItem;

    private BulkJobStoreInterface $bulkJobStore;

    private BulkJobIdGeneratorInterface $bulkJobIdGenerator;

    /**
     * @param StartBulkGenerateAwbItem $startBulkGenerateAwbItem
     * @param BulkJobStoreInterface $bulkJobStore
     * @param BulkJobIdGeneratorInterface $bulkJobIdGenerator
     */
    public function __construct(
        StartBulkGenerateAwbItem $startBulkGenerateAwbItem,
        BulkJobStoreInterface $bulkJobStore,
        BulkJobIdGeneratorInterface $bulkJobIdGenerator
    ) {
        $this->startBulkGenerateAwbItem = $startBulkGenerateAwbItem;
        $this->bulkJobStore = $bulkJobStore;
        $this->bulkJobIdGenerator = $bulkJobIdGenerator;
    }

    /**
     * @return StartBulkGenerateAwbItem
     */
    public function getStartBulkGenerateAwbItem(): StartBulkGenerateAwbItem
    {
        return $this->startBulkGenerateAwbItem;
    }

    /**
     * @return BulkJobStoreInterface
     */
    public function getBulkJobStore(): BulkJobStoreInterface
    {
        return $this->bulkJobStore;
    }

    /**
     * @return BulkJobIdGeneratorInterface
     */
    public function getBulkJobIdGenerator(): BulkJobIdGeneratorInterface
    {
        return $this->bulkJobIdGenerator;
    }
}
