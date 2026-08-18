<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Import\StartAllImport;

use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;

final class StartAllImportRequest
{
    private int $userId;

    private BulkJobStoreInterface $bulkJobStore;

    /**
     * @param int $userId
     * @param BulkJobStoreInterface $bulkJobStore
     */
    public function __construct(
        int $userId,
        BulkJobStoreInterface $bulkJobStore
    ) {
        $this->userId = $userId;
        $this->bulkJobStore = $bulkJobStore;
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
