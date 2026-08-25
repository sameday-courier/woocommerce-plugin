<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Application\UseCases\Import\StartAllImport;

use SamedayCourier\Shipping\Domain\Ports\BulkJobIdGeneratorInterface;
use SamedayCourier\Shipping\Domain\Ports\BulkJobStoreInterface;

final class StartAllImportRequest
{
    private int $userId;

    private BulkJobStoreInterface $bulkJobStore;

    private BulkJobIdGeneratorInterface $bulkJobIdGenerator;

    /**
     * @param int $userId
     * @param BulkJobStoreInterface $bulkJobStore
     * @param BulkJobIdGeneratorInterface $bulkJobIdGenerator
     */
    public function __construct(
        int $userId,
        BulkJobStoreInterface $bulkJobStore,
        BulkJobIdGeneratorInterface $bulkJobIdGenerator
    ) {
        $this->userId = $userId;
        $this->bulkJobStore = $bulkJobStore;
        $this->bulkJobIdGenerator = $bulkJobIdGenerator;
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

    /**
     * @return BulkJobIdGeneratorInterface
     */
    public function getBulkJobIdGenerator(): BulkJobIdGeneratorInterface
    {
        return $this->bulkJobIdGenerator;
    }
}
