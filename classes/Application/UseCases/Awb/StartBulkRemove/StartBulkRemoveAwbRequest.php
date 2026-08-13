<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Awb\StartBulkRemove;

use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;

if (!defined('ABSPATH')) {
    exit;
}

final class StartBulkRemoveAwbRequest
{
    private StartBulkRemoveAwbItem $startBulkRemoveAwbItem;

    private int $userId;

    private BulkJobStoreInterface $bulkJobStore;

    public function __construct(
        StartBulkRemoveAwbItem $startBulkRemoveAwbItem,
        int $userId,
        BulkJobStoreInterface $bulkJobStore
    ) {
        $this->startBulkRemoveAwbItem = $startBulkRemoveAwbItem;
        $this->userId = $userId;
        $this->bulkJobStore = $bulkJobStore;
    }

    public function getStartBulkRemoveAwbItem(): StartBulkRemoveAwbItem
    {
        return $this->startBulkRemoveAwbItem;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getBulkJobStore(): BulkJobStoreInterface
    {
        return $this->bulkJobStore;
    }
}
