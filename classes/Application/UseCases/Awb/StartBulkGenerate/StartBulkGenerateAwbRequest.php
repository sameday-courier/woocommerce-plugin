<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkGenerate;

use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;

final class StartBulkGenerateAwbRequest
{
    private StartBulkGenerateAwbItem $startBulkGenerateAwbItem;

    private int $userId;

    /**
     * @var BulkJobStoreInterface
     */
    private BulkJobStoreInterface $bulkJobStore;

    /**
     * @param StartBulkGenerateAwbItem $startBulkGenerateAwbItem
     * @param int $userId
     * @param BulkJobStoreInterface $bulkJobStore
     */
    public function __construct(
        StartBulkGenerateAwbItem $startBulkGenerateAwbItem,
        int $userId,
        BulkJobStoreInterface $bulkJobStore
    ) {
        $this->startBulkGenerateAwbItem = $startBulkGenerateAwbItem;
        $this->userId = $userId;
        $this->bulkJobStore = $bulkJobStore;
    }

    /**
     * @return StartBulkGenerateAwbItem
     */
    public function getStartBulkGenerateAwbItem(): StartBulkGenerateAwbItem
    {
        return $this->startBulkGenerateAwbItem;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @return BulkJobStoreInterface
     */
    public function getBulkJobStore(): BulkJobStoreInterface
    {
        return $this->bulkJobStore;
    }
}
