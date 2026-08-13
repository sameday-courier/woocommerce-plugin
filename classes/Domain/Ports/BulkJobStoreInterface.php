<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\BulkJob;

interface BulkJobStoreInterface
{
    /**
     * @param BulkJob $job
     *
     * @return void
     */
    public function create(BulkJob $job): void;

    /**
     * @param string $jobId
     * @param int $userId
     *
     * @return BulkJob|null
     */
    public function get(string $jobId, int $userId): ?BulkJob;

    /**
     * @param BulkJob $job
     *
     * @return void
     */
    public function save(BulkJob $job): void;

    /**
     * @param string $jobId
     * @param int $userId
     *
     * @return void
     */
    public function delete(string $jobId, int $userId): void;
}
