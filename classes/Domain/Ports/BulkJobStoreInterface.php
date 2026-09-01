<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\BulkJobDto;
use SamedayCourier\Shipping\Domain\ValueObject\BulkJobId;

interface BulkJobStoreInterface
{
    /**
     * @param BulkJobDto $job
     *
     * @return void
     */
    public function create(BulkJobDto $job): void;

    /**
     * @param BulkJobId $jobId
     * @param int $userId
     *
     * @return BulkJobDto|null
     */
    public function get(BulkJobId $jobId, int $userId): ?BulkJobDto;

    /**
     * @param BulkJobDto $job
     *
     * @return void
     */
    public function save(BulkJobDto $job): void;

    /**
     * @param BulkJobId $jobId
     * @param int $userId
     *
     * @return void
     */
    public function delete(BulkJobId $jobId, int $userId): void;
}
