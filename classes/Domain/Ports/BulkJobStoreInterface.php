<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\BulkJobDto;

interface BulkJobStoreInterface
{
    /**
     * @param BulkJobDto $job
     *
     * @return void
     */
    public function create(BulkJobDto $job): void;

    /**
     * @param string $jobId
     * @param int $userId
     *
     * @return BulkJobDto|null
     */
    public function get(string $jobId, int $userId): ?BulkJobDto;

    /**
     * @param BulkJobDto $job
     *
     * @return void
     */
    public function save(BulkJobDto $job): void;

    /**
     * @param string $jobId
     * @param int $userId
     *
     * @return void
     */
    public function delete(string $jobId, int $userId): void;
}
