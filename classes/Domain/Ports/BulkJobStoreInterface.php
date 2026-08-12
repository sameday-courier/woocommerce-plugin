<?php

declare(strict_types=1);

namespace SamedayCourier\Shipping\Domain\Ports;

use SamedayCourier\Shipping\Domain\DTOs\BulkJob;

if (!defined('ABSPATH')) {
    exit;
}

interface BulkJobStoreInterface
{
    public function create(BulkJob $job): void;

    public function get(string $jobId, int $userId): ?BulkJob;

    public function save(BulkJob $job): void;

    public function delete(string $jobId, int $userId): void;
}
